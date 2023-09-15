<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionEvent;

use Money\Money;

class PositionValueDto
{
    public ?Money $amount = null;

    public ?float $quantity = null;

    public static function factory(?Money $amount = null, ?float $quantity = null): PositionValueDto
    {
        $dto = new PositionValueDto();
        $dto->amount = $amount;
        $dto->quantity = $quantity;

        return $dto;
    }
}
