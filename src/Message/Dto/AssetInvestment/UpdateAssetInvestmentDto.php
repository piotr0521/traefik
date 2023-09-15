<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\AssetInvestment;

use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\Sponsor;

class UpdateAssetInvestmentDto
{
    public ?string $name = null;

    public ?Privacy $privacy = null;

    public ?Sponsor $sponsor = null;

    public ?string $website = null;

    public ?bool $isEvergreen = null;

    public ?string $term = null;

    public ?string $irr = null;

    public ?string $multiple = null;
}
