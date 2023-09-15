<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionBusiness;

use DateTime;
use Groshy\Entity\Tag;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreatePositionBusinessDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\Length(max: 250)]
    public ?string $description = null;

    #[Assert\Url]
    public ?string $website = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(value: 100)]
    public ?float $ownership = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(new DateTime())]
    public ?DateTime $originalDate = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(value: 999999999)]
    public ?string $originalValue = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(value: 999999999)]
    public ?string $currentValue = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(new DateTime())]
    public ?DateTime $valueDate = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
