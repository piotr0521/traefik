<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\Metric;

use DateTime;
use Money\Money;
use Webmozart\Assert\Assert;

final class CalculationLine
{
    public function __construct(
        private readonly DateTime $date,
        private readonly Money $before,
        private readonly Money $after,
        private readonly CalculationLineEvent $event,
        private readonly ?CalculationLine $previousLine = null
    ) {
        Assert::false(CalculationLineEvent::START == $this->event && !is_null($previousLine), 'Start line should not have previous event');
        Assert::false(CalculationLineEvent::START != $this->event && is_null($previousLine), 'Non start line should always have previous event');
        Assert::false(CalculationLineEvent::START == $this->event && !$before->isZero(), 'Before value for start line should always be 0');
        Assert::false(CalculationLineEvent::END == $this->event && !$before->equals($after), 'End event should have equal before and after market value');
        if (!is_null($previousLine) && CalculationLineEvent::END != $this->event) {
            Assert::greaterThan($date->diff($previousLine->getDate())->days, 0, 'Only end event can have the same date as previous event');
        }
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getAfter(): Money
    {
        return $this->after;
    }

    public function getBefore(): Money
    {
        return $this->before;
    }

    public function cashFlow(): Money
    {
        return $this->before->subtract($this->after);
    }

    public function getEvent(): CalculationLineEvent
    {
        return $this->event;
    }

    public function isStart(): bool
    {
        return CalculationLineEvent::START == $this->event;
    }

    public function isEnd(): bool
    {
        return CalculationLineEvent::END == $this->event;
    }

    public function getRateOfReturn(): ?string
    {
        // it's not possible to calculate anything for period start
        if (CalculationLineEvent::START == $this->event) {
            return null;
        }

        // it's not possible to calculate rate of zero
        if ($this->previousLine->getAfter()->isZero()) {
            return null;
        }

        return $this->before->ratioOf($this->previousLine->getAfter());
    }

    // Ignore previous line
    public function equals(CalculationLine $line): bool
    {
        return $this->getAfter()->equals($line->getAfter()) &&
        $this->getBefore()->equals($line->getBefore()) &&
        $this->getEvent() == $line->getEvent();
    }
}
