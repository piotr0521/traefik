<?php

declare(strict_types=1);

namespace Groshy\Model;

final class DashboardValue
{
    public ?string $topLevelTypeName = null;
    public ?string $topLevelTypeSlug = null;
    public ?bool $typeIsAsset = null;
    public ?string $typeName = null;
    public ?string $typeSlug = null;
    public ?string $assetName = null;
    public ?string $assetId = null;
    public ?string $positionId = null;
    public ?string $value = null;
    public ?float $quantity = null;
    public ?string $valueDate = null;

    public static function factory(
        string $topLevelTypeName,
        string $topLevelTypeSlug,
        bool $typeIsAsset,
        string $typeName,
        string $typeSlug,
        string $assetName,
        string $assetId,
        string $positionId,
        string $value,
        float $quantity,
        string $valueDate,
    ): DashboardValue {
        $dash = new DashboardValue();
        $dash->topLevelTypeName = $topLevelTypeName;
        $dash->topLevelTypeSlug = $topLevelTypeSlug;
        $dash->typeIsAsset = $typeIsAsset;
        $dash->typeName = $typeName;
        $dash->typeSlug = $typeSlug;
        $dash->assetId = $assetId;
        $dash->assetName = $assetName;
        $dash->positionId = $positionId;
        $dash->value = $value;
        $dash->quantity = $quantity;
        $dash->valueDate = $valueDate;

        return $dash;
    }
}
