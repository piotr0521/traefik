<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionEvent;

use Money\Money;

class CreateTransactionDto
{
    public ?Money $amount = null;

    public ?float $quantity = null;

    public static function factory(?Money $amount = null, ?float $quantity = null): CreateTransactionDto
    {
        $dto = new CreateTransactionDto();
        $dto->amount = $amount;
        $dto->quantity = $quantity;

        return $dto;
    }
}
