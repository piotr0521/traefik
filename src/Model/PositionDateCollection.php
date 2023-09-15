<?php

declare(strict_types=1);

namespace Groshy\Model;

use AppendIterator;
use ArrayIterator;
use CallbackFilterIterator;
use DateTime;
use Groshy\Domain\Calculation\Metric\CalculationLineCollection;
use Groshy\Domain\Calculation\Metric\CalculationLineEvent;
use Groshy\Entity\Transaction;
use Iterator;
use Money\Money;
use Webmozart\Assert\Assert;

final class PositionDateCollection
{
    /** @var array<string, PositionDateSet> */
    private array $list = [];

    /** @var array<PositionDateSet> */
    public function __construct(array $positionDateSets)
    {
        Assert::allIsInstanceOf($positionDateSets, PositionDateSet::class);
        Assert::minCount($positionDateSets, 1);

        foreach ($positionDateSets as $positionDateSet) {
            $key = $positionDateSet->getDate()->format('y-m-d');
            Assert::keyNotExists($this->list, $key);
            $this->list[$key] = $positionDateSet;
        }
    }

    public function getPositionDates(): Iterator
    {
        $iterator = new AppendIterator();
        array_walk($this->list, fn (PositionDateSet $el) => $iterator->append($el->getIterator()));

        return $iterator;
    }

    public function getPositions(): Iterator
    {
        $cache = [];
        $iterator = new CallbackFilterIterator($this->getPositionDates(), function (PositionDate $el) use (&$cache) {
            if (!isset($cache[strval($el->position->getId())])) {
                $cache[strval($el->position->getId())] = 1;

                return true;
            }

            return false;
        });

        // @todo find a better way to do this transformation
        return new ArrayIterator(array_map(fn (PositionDate $el) => $el->position, iterator_to_array($iterator, false)));
    }

    public function getPositionDateSet(DateTime $date): PositionDateSet
    {
        $key = $date->format('y-m-d');
        if (!isset($this->list[$key])) {
            throw new \RuntimeException(sprintf('Date %s does not exist in the dataset', $key));
        }

        return $this->list[$key];
    }

    public function getContributions(): Money
    {
        $result = Money::USD(0);
        /** @var PositionDate $positionDate */
        foreach ($this->getPositionDates() as $positionDate) {
            $result = $result->add($positionDate->sumAmountByCallback(fn (Transaction $t) => $t->getAmount()->isNegative()));
        }

        return $result->multiply(-1);
    }

    public function getDistributions(): Money
    {
        $result = Money::USD(0);
        /** @var PositionDate $positionDate */
        foreach ($this->getPositionDates() as $positionDate) {
            $result = $result->add($positionDate->sumAmountByCallback(fn (Transaction $t) => $t->getAmount()->isPositive()));
        }

        return $result;
    }

    public function getLineCollection(): CalculationLineCollection
    {
        $collection = new CalculationLineCollection();
        $data = array_values($this->list);
        for ($i = 0; $i < count($data); ++$i) {
            // try to find first non zero amount to use as a start
            if ($collection->isEmpty()) {
                if (!$data[$i]->getAmount()->isZero()) {
                    $collection->add($data[$i]->getDate(), Money::USD(0), $data[$i]->getAmount(), CalculationLineEvent::START);
                }
                continue;
            }

            $positionDataSet = $data[$i];
            if (!$positionDataSet->getChange()->isZero()) {
                $collection->add($data[$i]->getDate(), $data[$i]->getAmount()->add($positionDataSet->getChange()), $data[$i]->getAmount(), CalculationLineEvent::MOVEMENT);
            }
        }
        // list only has zero values, use first to create a start line
        if ($collection->isEmpty()) {
            $collection->add($data[0]->getDate(), Money::USD(0), Money::USD(0), CalculationLineEvent::START);
        }
        $lastIndex = count($data) - 1;
        $collection->add($data[$lastIndex]->getDate(), $data[$lastIndex]->getAmount(), $data[$lastIndex]->getAmount(), CalculationLineEvent::END);

        return $collection;
    }

    public function getRange(): DateRange
    {
        $start = $startNonZero = $end = null;
        foreach ($this->list as $element) {
            if (!$element->isZero() && (is_null($startNonZero) || $startNonZero > $element->getDate())) {
                $startNonZero = $element->getDate();
            }
            if (is_null($start) || $start > $element->getDate()) {
                $start = $element->getDate();
            }
            if (is_null($end) || $end < $element->getDate()) {
                $end = $element->getDate();
            }
        }

        return new DateRange($startNonZero ?: $start, $end);
    }

    public function getFullRange(): DateRange
    {
        $start = $end = null;
        foreach ($this->list as $element) {
            if (is_null($start) || $start > $element->getDate()) {
                $start = $element->getDate();
            }
            if (is_null($end) || $end < $element->getDate()) {
                $end = $element->getDate();
            }
        }

        return new DateRange($start, $end);
    }
}
