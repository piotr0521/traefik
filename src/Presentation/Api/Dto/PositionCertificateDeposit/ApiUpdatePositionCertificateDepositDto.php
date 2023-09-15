<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionCertificateDeposit;

use Groshy\Entity\Institution;
use Groshy\Entity\Tag;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdatePositionCertificateDepositDto
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\GreaterThanOrEqual(1)]
    #[Assert\LessThanOrEqual(60)]
    public ?int $terms = null;

    #[Assert\GreaterThan(0)]
    public ?int $yield = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public ?array $tags = null;
}
