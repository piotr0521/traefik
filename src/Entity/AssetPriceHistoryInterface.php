<?php

declare(strict_types=1);

namespace Groshy\Entity;

use DateTime;
use Money\Money;

interface AssetPriceHistoryInterface
{
    public function getAsset(): ?Asset;

    public function getPricedAt(): ?DateTime;

    public function getPrice(): ?Money;
}
