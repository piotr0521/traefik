<?php

declare(strict_types=1);

namespace Groshy\Entity;

class PositionCashAccountData
{
    protected ?float $yield = null;

    public function getYield(): ?float
    {
        return $this->yield;
    }

    public function setYield(?float $yield): void
    {
        $this->yield = $yield;
    }
}
