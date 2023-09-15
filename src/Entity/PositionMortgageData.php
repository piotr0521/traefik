<?php

declare(strict_types=1);

namespace Groshy\Entity;

class PositionMortgageData
{
    protected ?int $terms = null;
    protected ?float $interest = null;

    public function getTerms(): ?int
    {
        return $this->terms;
    }

    public function setTerms(?int $terms): void
    {
        $this->terms = $terms;
    }

    public function getInterest(): ?float
    {
        return $this->interest;
    }

    public function setInterest(?float $interest): void
    {
        $this->interest = $interest;
    }
}
