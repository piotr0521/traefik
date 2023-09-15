<?php

declare(strict_types=1);

namespace Groshy\Provider;

use DateInterval;
use DatePeriod;
use DateTime;
use Groshy\Entity\Position;
use Groshy\Entity\PositionValue;
use Groshy\Entity\Transaction;
use Groshy\Model\AssetListPriceCollection;
use Groshy\Model\PositionDate;
use Groshy\Model\PositionDateCollection;
use Groshy\Model\PositionDateSet;
use Groshy\Model\PositionDateValue;
use Money\Money;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class PositionDateCollectionFactory
{
    private ?AssetListPriceCollection $priceHistory;

    public function __construct(
        private readonly RepositoryInterface $positionValueRepository,
        private readonly RepositoryInterface $transactionRepository,
        private readonly AssetListPriceCollectionFactory $priceListFactory,
    ) {
    }

    public function build(array $positions, DateTime $from, DateTime $to): ?PositionDateCollection
    {
        $this->priceHistory = $this->priceListFactory->buildList($positions, $from, $to);
        $transactionCache = $this->buildTransactionCache($positions, $from, $to);
        $valueCache = $this->buildValueCache($positions, $from, $to);

        $dates = $this->getAllDates([$transactionCache, $valueCache]);
        $positionsDates = [];
        foreach ($positions as $position) {
            $previousValue = null;
            foreach ($dates as $date) {
                $key1 = $this->positionToKey($position);
                $key2 = $this->dateToKey($date);
                if (!isset($valueCache[$key1][$key2])) {
                    $positionsDates[$key2][] = new PositionDate(
                        $position,
                        $date,
                        $transactionCache[$key1][$key2] ?? [],
                        $previousValue,
                        $previousValue
                    );
                    continue;
                }
                $currentValue = $valueCache[$key1][$key2][0];
                if (!isset($positionsDates[$key2])) {
                    $positionsDates[$key2] = [];
                }
                $positionsDates[$key2][] = new PositionDate(
                    $position,
                    $date,
                    $transactionCache[$key1][$key2] ?? [],
                    $currentValue,
                    $previousValue
                );
                $previousValue = $currentValue ?: $previousValue;
            }
        }
        $sets = [];
        foreach ($positionsDates as $el) {
            $sets[] = new PositionDateSet($el);
        }
        if (count($sets) > 0) {
            return new PositionDateCollection($sets);
        }

        return null;
    }

    private function buildTransactionCache(array $positions, DateTime $from, DateTime $to): array
    {
        $result = [];
        /** @var array<Transaction> $transactions */
        $transactions = $this->transactionRepository->findByPositionsAndInterval($positions, $from, $to);
        foreach ($transactions as $transaction) {
            $this->addValue(
                $result,
                $transaction->getPosition(),
                $transaction->getTransactionDate(),
                $transaction
            );
        }

        return $result;
    }

    private function buildValueCache(array $positions, DateTime $from, DateTime $to): array
    {
        $result = [];
        /** @var PositionValue $el */
        foreach ($this->positionValueRepository->findAllByPositionsAndInterval($positions, $from, $to) as $el) {
            $this->addValueFromPositionValue($result, $el);
        }
        $this->buildValueCacheStartDates($result, $positions, $from);
        $this->buildValueCacheZeroStart($result, $positions, $from);
        $this->buildValueCacheForDailyPricedAssets($result, $positions, $from, $to);
        $this->buildValueCacheEnd($result, $positions, $to);
        foreach ($result as $key => $data) {
            ksort($result[$key]);
        }

        return $result;
    }

    private function dateToKey(DateTime $date): string
    {
        return $date->format('Y-m-d');
    }

    private function positionToKey(Position $position): string
    {
        return strval($position->getId());
    }

    private function positionToAssetKey(Position $position): string
    {
        return strval($position->getAsset()->getId());
    }

    private function addValue(array &$cache, Position $position, DateTime $date, $value): void
    {
        $key1 = $this->positionToKey($position);
        $key2 = $this->dateToKey($date);
        if (!isset($cache[$key1])) {
            $cache[$key1] = [];
        }
        if (!isset($cache[$key1][$key2])) {
            $cache[$key1][$key2] = [];
        }
        $cache[$key1][$key2][] = $value;
    }

    private function addValueFromPositionValue(array &$cache, PositionValue $positionValue): void
    {
        $this->addValue(
            $cache,
            $positionValue->getPosition(),
            $positionValue->getDate(),
            $this->buildPositionDateValue(
                $positionValue->getPosition(),
                $positionValue->getDate(),
                $positionValue->getAmount(),
                $positionValue->getQuantity()
            )
        );
    }

    private function getAllDates(array $caches): iterable
    {
        $result = [];
        foreach ($caches as $cache) {
            foreach (array_keys($cache) as $key) {
                foreach (array_keys($cache[$key]) as $date) {
                    $result[$date] = 1;
                }
            }
        }
        ksort($result);

        return array_map(fn (string $date) => DateTime::createFromFormat('Y-m-d', $date), array_keys($result));
    }

    // build initial values for positions started before the interval start date
    private function buildValueCacheStartDates(array &$cache, array $positions, DateTime $before): void
    {
        // Find start values for positions started before "from" date
        /** @var PositionValue $el */
        foreach ($this->positionValueRepository->findLastByPositionsAndBeforeDate($positions, $before) as $el) {
            $this->addValue(
                $cache,
                $el->getPosition(),
                $before,
                $this->buildPositionDateValue(
                    $el->getPosition(),
                    $before,
                    $el->getAmount(),
                    $el->getQuantity()
                )
            );
        }
    }

    // builds initial 0 values for positions started in the middle of the interval
    private function buildValueCacheZeroStart(array &$cache, array $positions, DateTime $before): void
    {
        foreach ($positions as $position) {
            if (is_null($position->getStartDate()) || $position->getStartDate() > $before) {
                $this->addValue($cache, $position, $before, new PositionDateValue(Money::USD('0')));
            }
        }
    }

    // builds values for assets with daily prices: securities and crypto
    private function buildValueCacheForDailyPricedAssets(array &$cache, array $positions, DateTime $from, DateTime $to): void
    {
        $period = $this->generatePeriod($from, $to);
        foreach ($positions as $position) {
            $key1 = $this->positionToKey($position);
            if (!$this->priceHistory->containsKey($key1)) {
                continue;
            }
            /** @var PositionDateValue $previousValue */
            $previousValue = $cache[$key1][$this->dateToKey($from)]->getValue();
            foreach ($period as $date) {
                $key2 = $this->dateToKey($date);
                if (isset($cache[$key1][$key2])) {
                    $previousValue = $cache[$key1][$key2];
                    continue;
                }
                if ($this->priceHistory->get($key1)->containsKey($key2)) {
                    $previousValue = $this->buildPositionDateValue(
                        $position,
                        $date,
                        $previousValue->amount,
                        $previousValue->quantity
                    );
                    $this->addValue($cache, $position, $date, $previousValue);
                }
            }
        }
    }

    // builds last day of the interval for all positions
    private function buildValueCacheEnd(array &$cache, array $positions, DateTime $to): void
    {
        $key2 = $this->dateToKey($to);
        foreach ($positions as $position) {
            $key1 = $this->positionToKey($position);
            if (isset($cache[$key1][$key2])) {
                continue;
            }
            if (!isset($cache[$key1])) {
                continue;
            }
            ksort($cache[$key1]);
            $lastPositionValue = end($cache[$key1])[0];
            $this->addValue($cache, $position, $to, $lastPositionValue);
        }
    }

    private function getPrice(Position $position, DateTime $date): ?Money
    {
        $key1 = $this->positionToAssetKey($position);
        $key2 = $this->dateToKey($date);
        if (!$this->priceHistory->containsKey($key1)) {
            return null;
        }

        return $this->priceHistory->get($key1)->get($key2);
    }

    private function buildPositionDateValue(Position $position, DateTime $date, ?Money $amount = null, ?float $quantity = null): PositionDateValue
    {
        if (is_null($quantity) || !is_null($amount)) {
            return new PositionDateValue($amount, $quantity);
        }
        $price = $this->getPrice($position, $date);
        if (is_null($price)) {
            throw new \RuntimeException('Price is not found');
        }

        return new PositionDateValue($price->multiply(strval($quantity)), $quantity);
    }

    private function generatePeriod(DateTime $from, DateTime $to): DatePeriod
    {
        return new DatePeriod(
            $from,
            new DateInterval('P1D'),
            $to,
            DatePeriod::EXCLUDE_START_DATE
        );
    }
}
