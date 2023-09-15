<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionInvestment;

use Groshy\Entity\AssetInvestment;
use Groshy\Entity\Institution;
use Groshy\Entity\Tag;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Groshy\Validator\Constraints\Money;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreatePositionInvestmentDto implements CreatedByInjectable
{
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Sequentially([
            new Money(),
            new Assert\GreaterThan(0.01),
        ]),
    ])]
    public ?string $capitalCommitment = null;

    public bool $isDirect = false;

    public ?Institution $institution = null;

    #[Assert\NotBlank]
    public ?AssetInvestment $asset = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
