<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\AssetInvestment;

use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\AssetInvestment;
use Groshy\Entity\AssetType;
use Groshy\Entity\Sponsor;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Groshy\Validator\Constraints as GroshyAssert;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreateAssetInvestmentDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [Privacy::class, 'choices'])]
    public ?string $privacy = null;

    #[Assert\NotBlank]
    public ?Sponsor $sponsor = null;

    #[Assert\NotBlank]
    #[GroshyAssert\AssetTypeMatch(assetClass: AssetInvestment::class)]
    public ?AssetType $assetType = null;

    #[Assert\Url]
    public ?string $website = null;

    public bool $isEvergreen = false;

    public ?string $term = null;

    public ?string $irr = null;

    public ?string $multiple = null;

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
