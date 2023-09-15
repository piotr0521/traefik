<?php

declare(strict_types=1);

namespace Groshy\Entity;

use Groshy\Domain\Enum\Privacy;

class AssetConfig
{
    public function __construct(
        private readonly Privacy $defaultPrivacy = Privacy::PUBLIC,
        private readonly bool $allowPrivacyChange = true,
    ) {
    }

    public function getDefaultPrivacy(): Privacy
    {
        return $this->defaultPrivacy;
    }

    public function isAllowPrivacyChange(): bool
    {
        return $this->allowPrivacyChange;
    }
}
