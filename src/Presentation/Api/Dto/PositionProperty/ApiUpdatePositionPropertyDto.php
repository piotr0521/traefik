<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionProperty;

use Groshy\Domain\Enum\PropertyType;
use Groshy\Entity\Tag;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdatePositionPropertyDto
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\Choice(callback: [PropertyType::class, 'choices'])]
    public ?string $propertyType = null;

    #[Assert\Url]
    public ?string $website = null;

    #[Assert\Length(max: 1024)]
    public ?string $address = null;

    #[Assert\LessThanOrEqual(value: 99999)]
    public ?int $units = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public ?array $tags = null;
}
