<?php

declare(strict_types=1);

namespace Groshy\Model;

final class AttributeModel
{
    public const POSITION = 'position';
    public const ROOT_TYPE = 'root_type';
    public const TYPE = 'type';
    public const TOTAL = 'total';
    public const BALANCE = 'balance';

    public const BALANCE_ASSET = 'asset';
    public const BALANCE_LIABILITY = 'liability';

    public function __construct(
        public readonly mixed $type,
        public readonly mixed $id,
        public readonly string $name,
        // Should we track allocation for this model
        public readonly bool $trackable = true,
        public readonly bool $isAsset = true,
    ) {
    }

    public function isTotal(): bool
    {
        return self::TOTAL == $this->type;
    }

    /**
     * @return array<AttributeModel>
     */
    public static function factory(DashboardValue $value): array
    {
        $return = [
            AttributeModel::forPosition($value),
            AttributeModel::forType($value),
            AttributeModel::total(),
            AttributeModel::forBalance($value->typeIsAsset),
        ];
        if ($value->typeIsAsset) {
            $return[] = AttributeModel::forRootType($value);
        }

        return $return;
    }

    public static function forBalance(bool $isAsset): AttributeModel
    {
        return new AttributeModel(AttributeModel::BALANCE, intval($isAsset), $isAsset ? self::BALANCE_ASSET : self::BALANCE_LIABILITY, false, $isAsset);
    }

    public static function total(): AttributeModel
    {
        return new AttributeModel(AttributeModel::TOTAL, AttributeModel::TOTAL, AttributeModel::TOTAL, false);
    }

    public static function forPosition(DashboardValue $value): AttributeModel
    {
        return new AttributeModel(AttributeModel::POSITION, $value->positionId, $value->assetName, true, $value->typeIsAsset);
    }

    private static function forRootType(DashboardValue $value): AttributeModel
    {
        return new AttributeModel(AttributeModel::ROOT_TYPE, $value->topLevelTypeSlug, $value->topLevelTypeName, true, $value->typeIsAsset);
    }

    private static function forType(DashboardValue $value): AttributeModel
    {
        return new AttributeModel(AttributeModel::TYPE, $value->typeSlug, $value->typeName, true, $value->typeIsAsset);
    }
}
