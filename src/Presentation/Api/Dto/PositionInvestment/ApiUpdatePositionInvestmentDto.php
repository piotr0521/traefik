<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionInvestment;

use Groshy\Entity\Institution;
use Groshy\Entity\Tag;
use Groshy\Validator\Constraints\Money;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdatePositionInvestmentDto
{
    #[Assert\Sequentially([
        new Assert\NotBlank(allowNull: true),
        new Assert\Sequentially([
            new Money(),
            new Assert\GreaterThan(0.01),
        ]),
    ])]
    public ?string $capitalCommitment = null;

    public ?bool $isDirect = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public ?array $tags = null;
}
