<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\ValueObject;

use DateInterval;
use DatePeriod;
use DateTime;
use Webmozart\Assert\Assert;

final class DateRange
{
    public function __construct(
        private readonly DateTime $start,
        private readonly DateTime $end,
    ) {
        Assert::true($end > $start);
    }

    public function diff(): DateInterval
    {
        return $this->end->diff($this->start);
    }

    public function getDatePeriod(string $interval = '1 day'): DatePeriod
    {
        $interval = DateInterval::createFromDateString($interval);
        $lastDataEnd = (clone $this->end)->add($interval);

        return new DatePeriod($this->start, $interval, $lastDataEnd);
    }

    public function getStart(): DateTime
    {
        return $this->start;
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }
}
