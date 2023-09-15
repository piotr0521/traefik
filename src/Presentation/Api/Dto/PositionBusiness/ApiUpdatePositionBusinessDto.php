<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionBusiness;

use Groshy\Entity\Tag;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdatePositionBusinessDto
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\Length(max: 250)]
    public ?string $description = null;

    #[Assert\Url]
    public ?string $website = null;

    #[Assert\NotBlank(allowNull: true)]
    #[Assert\LessThanOrEqual(value: 100)]
    public ?float $ownership = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public ?array $tags = null;
}
