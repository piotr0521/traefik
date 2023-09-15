<?php

declare(strict_types=1);

namespace Groshy\Provider;

use CallbackFilterIterator;
use DateTime;
use Groshy\Calculator\RoiCalculator;
use Groshy\Domain\Calculation\Metric\CompoundAnnualGrowthRateCalculator;
use Groshy\Domain\Calculation\Metric\TimeWeightedReturnCalculator;
use Groshy\Domain\Calculation\Metric\XirrCalculator;
use Groshy\Entity\Position;
use Groshy\Entity\PositionCreditCard;
use Groshy\Entity\PositionStatsData;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Model\PositionDateCollection;
use Money\Money;

// Class to build stats information about a set of positions
final class PositionStatsFactoryDeprecated
{
    use MoneyAwareTrait;

    public function build(PositionDateCollection $collection, DateTime $from, DateTime $to): array
    {
        $result = new PositionStatsData(count: 0);
        /** @var Position $position */
        foreach ($collection->getPositions() as $position) {
            $result->merge($position->getStatsData($from, $to));
        }

        $data = array_merge(
            $this->calculateReturns($collection),
            $this->calculateCreditCard($collection),
        );
        $data['contributions'] = $this->formatMoney($collection->getContributions());
        $data['distributions'] = $this->formatMoney($collection->getDistributions());
        $data['dates'] = $this->defineDates($collection);
        foreach ($result->getIterator() as $field => $value) {
            $data[$field] = $value instanceof Money ? $this->formatMoney($value) : $value;
        }

        return $data;
    }

    private function calculateReturns(PositionDateCollection $collection): array
    {
        $lines = $collection->getLineCollection()->toArray();
        $twrCalculator = new TimeWeightedReturnCalculator($lines);
        $data['twr'] = $twrCalculator->result();
        $range = $collection->getRange();
        if ($range->diff()->days > 365) {
            $cagrCalculator = new CompoundAnnualGrowthRateCalculator($twrCalculator, $range);
            $data['atwr'] = $cagrCalculator->result();
        }

        $xirr = new XirrCalculator($lines);
        $data['xirr'] = $xirr->result();

        $roi = new RoiCalculator($collection);
        $data['roi'] = [
            'amount' => $this->formatMoney($roi->result()['amount']),
            'percent' => $roi->result()['percent'],
        ];

        return $data;
    }

    private function defineDates(PositionDateCollection $collection): array
    {
        $startDate = new DateTime();
        $endDate = null;
        /** @var Position $position */
        foreach ($collection->getPositions() as $position) {
            if (!is_null($position->getStartDate()) && (is_null($startDate) || $startDate > $position->getStartDate())) {
                $startDate = $position->getStartDate();
            }
            $completeDate = is_null($position->getCompleteDate()) ? new DateTime() : $position->getCompleteDate();
            if (is_null($endDate) || $endDate < $completeDate) {
                $endDate = $completeDate;
            }
        }

        return [
            'minDate' => $startDate->format(DATE_ATOM),
            'maxDate' => $endDate->format(DATE_ATOM),
        ];
    }

    private function calculateCreditCard(PositionDateCollection $collection): array
    {
        $limit = Money::USD(0);
        $balance = Money::USD(0);
        $iterator = new CallbackFilterIterator($collection->getPositions(), fn ($p) => $p instanceof PositionCreditCard);
        /** @var PositionCreditCard $position */
        foreach ($iterator as $position) {
            if (!is_null($position->getCardLimit())) {
                $limit = $limit->add($position->getCardLimit());
                if (!is_null($position->getLastValue())) {
                    $balance = $balance->add($position->getLastValue()->getAmount());
                }
            }
        }
        if ($limit->isZero()) {
            return [];
        }

        return [
            'limit' => $this->formatMoney($limit),
            'utilization' => $balance->ratioOf($limit),
        ];
    }
}
