<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\Graph;

use DateTime;
use Groshy\Domain\Calculation\ValueObject\DateRange;
use Groshy\Domain\Calculation\ValueObject\DateValueAwareInterface;
use Groshy\Domain\Calculation\ValueObject\ValueList;
use function PHPUnit\Framework\assertArrayHasKey;

// Class to build a set of values for continuous set of dates
final class RangeValueListBuilder
{
    private array $dates;

    private mixed $default = 0;

    public function __construct(DateRange $range)
    {
        foreach ($range->getDatePeriod() as $day) {
            $this->dates[$this->dateToKey($day)] = null;
        }
    }

    public function add(array $values): RangeValueListBuilder
    {
        array_map(fn (DateValueAwareInterface $value) => $this->addValue($value), $values);

        return $this;
    }

    public function setDefault(mixed $default): RangeValueListBuilder
    {
        $this->default = $default;

        return $this;
    }

    public function build(): ValueList
    {
        $previous = $this->default;
        foreach ($this->dates as $key => $value) {
            if (is_null($value)) {
                $this->dates[$key] = $previous;
            } else {
                $previous = $value;
            }
        }

        return new ValueList($this->dates);
    }

    // Adds a value to the graph, existing values are updated
    private function addValue(DateValueAwareInterface $value): void
    {
        $key = $this->dateToKey($value->getDate());
        assertArrayHasKey($key, $this->dates, 'Date is not in the range');
        $this->dates[$key] = $value->getValue();
    }

    private function dateToKey(DateTime $date): string
    {
        return $date->format('Y-m-d');
    }
}
