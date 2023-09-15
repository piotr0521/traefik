<?php

declare(strict_types=1);

namespace Groshy\Model;

use DateInterval;
use DateTime;
use Webmozart\Assert\Assert;

final class DateRange
{
    public function __construct(
        private readonly DateTime $start,
        private readonly DateTime $end,
    ) {
        Assert::true($end >= $start);
    }

    public function diff(): DateInterval
    {
        return $this->end->diff($this->start);
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
