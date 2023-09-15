<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionInvestment;

use Groshy\Entity\Institution;
use Money\Money;

class UpdatePositionInvestmentDto
{
    public ?Money $capitalCommitment = null;

    public ?bool $isDirect = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    public ?array $tags = null;
}
