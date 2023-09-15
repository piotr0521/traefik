<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionSecurity;

use DateTime;
use Groshy\Entity\AssetSecurity;
use Groshy\Entity\Institution;
use Groshy\Entity\Tag;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreatePositionSecurityDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(new DateTime())]
    public ?DateTime $purchaseDate = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(value: 999999999)]
    #[Assert\GreaterThan(value: 0)]
    public ?float $quantity = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(value: 999999999)]
    #[Assert\GreaterThan(value: 0)]
    public ?string $averagePrice = null;

    #[Assert\NotBlank]
    public ?AssetSecurity $asset = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
