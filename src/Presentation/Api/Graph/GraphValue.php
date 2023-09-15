<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Graph;

use ApiPlatform\Metadata\ApiProperty;
use DateTime;
use Money\Money;

final class GraphValue
{
    public function __construct(private readonly DateTime $date, private readonly Money $amount)
    {
    }

    #[ApiProperty(identifier: true)]
    public function getDate(): string
    {
        return $this->date->format('Y-m-d');
    }

    #[ApiProperty]
    public function getAmount(): Money
    {
        return $this->amount;
    }
}
