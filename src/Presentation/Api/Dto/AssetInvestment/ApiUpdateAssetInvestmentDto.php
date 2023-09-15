<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\AssetInvestment;

use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\Sponsor;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdateAssetInvestmentDto
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\Choice(callback: [Privacy::class, 'choices'])]
    public ?string $privacy = null;

    public ?Sponsor $sponsor = null;

    #[Assert\Url]
    public ?string $website = null;

    public ?bool $isEvergreen = null;

    public ?string $term = null;

    public ?string $irr = null;

    public ?string $multiple = null;
}
