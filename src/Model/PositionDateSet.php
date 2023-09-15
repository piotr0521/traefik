<?php

declare(strict_types=1);

namespace Groshy\Model;

use ArrayIterator;
use DateTime;
use Iterator;
use Money\Money;
use Webmozart\Assert\Assert;

// Represents a set of PositionDate objects for one day
final class PositionDateSet
{
    /** @var array<PositionDate> */
    private array $list = [];

    private DateTime $date;

    /** @var array<PositionDate> */
    public function __construct(array $positionDates)
    {
        Assert::allIsInstanceOf($positionDates, PositionDate::class);
        Assert::minCount($positionDates, 1);
        Assert::keyExists($positionDates, 0);
        $this->date = $positionDates[0]->getDate();
        foreach ($positionDates as $positionDate) {
            Assert::eq($this->date->format('Y-m-d'), $positionDate->getDate()->format('Y-m-d'), 'PositionDateSet can only store information about one date');
            $this->list[] = $positionDate;
        }
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->list);
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    // Defines if there are any values for the current date
    public function isZero(): bool
    {
        foreach ($this->list as $element) {
            if (!$element->isZero()) {
                return false;
            }
        }

        return true;
    }

    public function getChange(): Money
    {
        return Money::sum(Money::USD(0), ...array_map(
            fn (PositionDate $pd) => $pd->sumAmountByCallback(fn ($t) => true),
            $this->list
        ));
    }

    public function getAmount(): Money
    {
        return Money::sum(Money::USD(0), ...array_map(
            fn (PositionDate $pd) => !is_null($pd->getValue()) ? $pd->getValue()->getAmount() : Money::USD(0),
            $this->list
        ));
    }
}
