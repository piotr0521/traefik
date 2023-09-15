<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\ValueObject;

use DateTime;

// Class represents a date and quantity pair. Can be used to store values for quantity assets: stock and crypto
final class DateValueQuantity implements DateValueAwareInterface
{
    public function __construct(
        private readonly DateTime $date,
        private readonly string $quantity
    ) {
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getValue(): string
    {
        return $this->quantity;
    }
}
