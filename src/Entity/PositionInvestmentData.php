<?php

declare(strict_types=1);

namespace Groshy\Entity;

use Money\Money;

class PositionInvestmentData
{
    protected ?Money $capitalCommitment = null;

    protected bool $isDirect = false;

    public function getCapitalCommitment(): ?Money
    {
        return $this->capitalCommitment;
    }

    public function setCapitalCommitment(?Money $capitalCommitment): void
    {
        $this->capitalCommitment = $capitalCommitment;
    }

    public function isDirect(): bool
    {
        return $this->isDirect;
    }

    public function setIsDirect(bool $isDirect): void
    {
        $this->isDirect = $isDirect;
    }
}
