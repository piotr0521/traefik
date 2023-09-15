<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Model;

use Groshy\Model\DashboardValue;

trait DashboardValueTrait
{
    private function getAssetDashValue(
        ?string $topLevelTypeName = 'Top Level Type 1',
        ?string $topLevelTypeSlug = 'top_slug1',
        ?bool $typeIsAsset = true,
        ?string $typeName = 'Type 1',
        ?string $typeSlug = 'slug1',
        ?string $assetName = 'Asset 1',
        ?string $assetId = '22',
        ?string $positionId = '1',
        ?string $value = '100',
        ?float $quantity = 0,
        ?string $valueDate = '2022-06-01',
    ): DashboardValue {
        return DashboardValue::factory($topLevelTypeName, $topLevelTypeSlug, $typeIsAsset, $typeName, $typeSlug, $assetName, $assetId, $positionId, $value, $quantity, $valueDate);
    }

    private function getLiabilityDashValue(
        ?string $topLevelTypeName = 'Top Level Type 2',
        ?string $topLevelTypeSlug = 'top_slug2',
        ?bool $typeIsAsset = false,
        ?string $typeName = 'Type 2',
        ?string $typeSlug = 'slug2',
        ?string $assetName = 'Liability 1',
        ?string $assetId = '33',
        ?string $positionId = '2',
        ?string $value = '100',
        ?float $quantity = 0,
        ?string $valueDate = '2022-06-01',
    ): DashboardValue {
        return DashboardValue::factory($topLevelTypeName, $topLevelTypeSlug, $typeIsAsset, $typeName, $typeSlug, $assetName, $assetId, $positionId, $value, $quantity, $valueDate);
    }
}
