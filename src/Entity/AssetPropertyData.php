<?php

declare(strict_types=1);

namespace Groshy\Entity;

use Groshy\Domain\Enum\PropertyType;

class AssetPropertyData
{
    protected ?PropertyType $propertyType = null;

    protected ?string $website = null;

    protected ?string $address = null;

    protected ?int $units = null;

    public function getPropertyType(): ?PropertyType
    {
        return $this->propertyType;
    }

    public function setPropertyType(?PropertyType $propertyType): void
    {
        $this->propertyType = $propertyType;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getUnits(): ?int
    {
        return $this->units;
    }

    public function setUnits(?int $units): void
    {
        $this->units = $units;
    }
}
