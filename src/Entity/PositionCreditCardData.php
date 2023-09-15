<?php

declare(strict_types=1);

namespace Groshy\Entity;

use Money\Money;

class PositionCreditCardData
{
    protected ?Money $cardLimit = null;

    public function getCardLimit(): ?Money
    {
        return $this->cardLimit;
    }

    public function setCardLimit(?Money $cardLimit): void
    {
        $this->cardLimit = $cardLimit;
    }
}
