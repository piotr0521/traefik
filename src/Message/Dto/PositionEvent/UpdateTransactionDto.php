<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionEvent;

use Groshy\Entity\Transaction;
use Money\Money;

class UpdateTransactionDto
{
    public ?Transaction $transaction = null;

    public ?Money $amount = null;

    public ?float $quantity = null;

    public function hasValue(): bool
    {
        return !is_null($this->amount) || !is_null($this->quantity);
    }
}
