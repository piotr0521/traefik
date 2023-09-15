<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\Metric;

use DateTime;
use Money\Money;

// Helper class to create a collection of CalculationLine
final class CalculationLineCollection
{
    /** @var array<CalculationLine> */
    private array $lines = [];

    public function __construct(array $data = [])
    {
        $this->addArray($data);
    }

    public function add(DateTime $date, Money $before, Money $after, CalculationLineEvent $event): void
    {
        $last = count($this->lines) > 0 ? $this->lines[count($this->lines) - 1] : null;
        $this->lines[] = new CalculationLine($date, $before, $after, $event, $last);
    }

    public function addArray(array $data): void
    {
        foreach ($data as $element) {
            $this->add($element[0], $element[1], $element[2], $element[3]);
        }
    }

    public function toArray(): array
    {
        return $this->lines;
    }

    public function isEmpty(): bool
    {
        return 0 == count($this->lines);
    }

    public function equals(CalculationLineCollection $collection): bool
    {
        if (count($this->lines) != count($collection->toArray())) {
            return false;
        }
        $colArray = $collection->toArray();
        foreach ($this->lines as $key => $line) {
            if (!$line->equals($colArray[$key])) {
                return false;
            }
        }

        return true;
    }
}
