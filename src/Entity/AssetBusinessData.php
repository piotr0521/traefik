<?php

declare(strict_types=1);

namespace Groshy\Entity;

class AssetBusinessData
{
    protected ?string $website = null;

    protected ?string $description = null;

    protected ?float $ownership = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getOwnership(): ?float
    {
        return $this->ownership;
    }

    public function setOwnership(?float $ownership): void
    {
        $this->ownership = $ownership;
    }
}
