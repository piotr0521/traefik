<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionProperty;

use Groshy\Domain\Enum\PropertyType;

class UpdatePositionPropertyDto
{
    public ?string $name = null;

    public ?PropertyType $propertyType = null;

    public ?string $website = null;

    public ?string $address = null;

    public ?int $units = null;

    public ?string $notes = null;

    public ?array $tags = null;
}
