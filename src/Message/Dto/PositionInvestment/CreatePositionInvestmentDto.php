<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionInvestment;

use Groshy\Entity\AssetInvestment;
use Groshy\Entity\Institution;
use Money\Money;
use Talav\Component\User\Model\UserInterface;

class CreatePositionInvestmentDto
{
    public ?Money $capitalCommitment = null;

    public bool $isDirect = false;

    public ?Institution $institution = null;

    public ?AssetInvestment $asset = null;

    public ?UserInterface $createdBy = null;

    public ?string $notes = null;

    public array $tags = [];
}
