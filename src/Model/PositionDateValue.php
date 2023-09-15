<?php

declare(strict_types=1);

namespace Groshy\Model;

use Money\Money;
use Webmozart\Assert\Assert;

final class PositionDateValue
{
    public function __construct(
        public readonly ?Money $amount = null,
        public readonly ?float $quantity = null,
    ) {
        Assert::true(!is_null($amount) || !is_null($quantity));
    }

    public function eq(PositionDateValue $value): bool
    {
        return $this->eqMoney($value->amount) && $this->eqQuantity($value->quantity);
    }

    public function getAmount(): ?Money
    {
        return $this->amount;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    private function eqMoney(?Money $amount = null): bool
    {
        if (is_null($this->amount) && is_null($amount)) {
            return true;
        }
        if ($this->amount instanceof Money && $amount instanceof Money) {
            return $this->amount->equals($amount);
        }

        return false;
    }

    private function eqQuantity(?float $quantity = null): bool
    {
        return $this->quantity == $quantity;
    }
}
