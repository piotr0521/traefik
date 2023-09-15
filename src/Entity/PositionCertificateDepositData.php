<?php

declare(strict_types=1);

namespace Groshy\Entity;

class PositionCertificateDepositData
{
    protected ?int $terms = null;
    protected ?float $yield = null;

    public function getTerms(): ?int
    {
        return $this->terms;
    }

    public function setTerms(?int $terms): void
    {
        $this->terms = $terms;
    }

    public function getYield(): ?float
    {
        return $this->yield;
    }

    public function setYield(?float $yield): void
    {
        $this->yield = $yield;
    }
}
