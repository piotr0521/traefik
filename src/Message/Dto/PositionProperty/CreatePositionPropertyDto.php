<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionProperty;

use DateTime;
use Groshy\Domain\Enum\PropertyType;
use Groshy\Entity\AssetProperty;
use Groshy\Entity\Tag;
use Money\Money;
use Talav\Component\User\Model\UserInterface;

class CreatePositionPropertyDto
{
    public ?string $name = null;

    public ?PropertyType $propertyType = null;

    public ?string $website = null;

    public ?string $address = null;

    public ?int $units = null;

    public ?DateTime $purchaseDate = null;

    public ?Money $purchaseValue = null;

    public ?Money $currentValue = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];

    public ?UserInterface $createdBy = null;

    public ?AssetProperty $asset = null;
}
