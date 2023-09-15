<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\ValueObject;

use DateTime;
use Money\Money;

// Class represents a date and money pair. Can be used to store graph values or stock and crypto prices
final class DateValueAmount implements DateValueAwareInterface
{
    public function __construct(
        private readonly DateTime $date,
        private readonly Money $amount
    ) {
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getValue(): Money
    {
        return $this->amount;
    }
}
