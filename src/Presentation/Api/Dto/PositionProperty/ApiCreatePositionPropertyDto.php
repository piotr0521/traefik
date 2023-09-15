<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionProperty;

use DateTime;
use Groshy\Domain\Enum\PropertyType;
use Groshy\Entity\Tag;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreatePositionPropertyDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [PropertyType::class, 'choices'])]
    public ?string $propertyType = null;

    #[Assert\Url]
    public ?string $website = null;

    #[Assert\Length(max: 1024)]
    public ?string $address = null;

    #[Assert\LessThanOrEqual(value: 99999)]
    public ?int $units = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(new DateTime())]
    public ?DateTime $purchaseDate = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(value: 999999999)]
    public ?string $purchaseValue = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(value: 999999999)]
    public ?string $currentValue = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
