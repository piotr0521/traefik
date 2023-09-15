<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\AssetInvestment;

use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\AssetType;
use Groshy\Entity\Sponsor;
use Talav\Component\User\Model\UserInterface;

class CreateAssetInvestmentDto
{
    public ?string $name = null;

    public ?Privacy $privacy = null;

    public ?Sponsor $sponsor = null;

    public ?AssetType $assetType = null;

    public ?UserInterface $createdBy = null;

    public ?string $website = null;

    public bool $isEvergreen = false;

    public ?string $term = null;

    public ?string $irr = null;

    public ?string $multiple = null;
}
