<?php

declare(strict_types=1);

namespace Groshy\Entity;

class AssetInvestmentData
{
    protected ?string $website = null;

    protected bool $isEvergreen = false;

    protected ?string $term = null;

    protected ?string $irr = null;

    protected ?string $multiple = null;

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function isEvergreen(): bool
    {
        return $this->isEvergreen;
    }

    public function setIsEvergreen(bool $isEvergreen): void
    {
        $this->isEvergreen = $isEvergreen;
    }

    public function getTerm(): ?string
    {
        return $this->term;
    }

    public function setTerm(?string $term): void
    {
        $this->term = $term;
    }

    public function getIrr(): ?string
    {
        return $this->irr;
    }

    public function setIrr(?string $irr): void
    {
        $this->irr = $irr;
    }

    public function getMultiple(): ?string
    {
        return $this->multiple;
    }

    public function setMultiple(?string $multiple): void
    {
        $this->multiple = $multiple;
    }
}
