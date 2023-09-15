<?php

declare(strict_types=1);

namespace Groshy\Mapper\Model;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Groshy\Model\DashboardValue;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Model\PositionDate;
use Webmozart\Assert\Assert;

class PositionDateToDashboardValueMapper extends CustomMapper
{
    use MoneyAwareTrait;

    public function mapToObject($source, $destination)
    {
        /* @var PositionDate $source */
        Assert::isInstanceOf($source, PositionDate::class);
        /* @var DashboardValue $destination */
        Assert::isInstanceOf($destination, DashboardValue::class);

        $position = $source->position;
        $type = $position->getAsset()->getAssetType();
        $rootType = $type->isTopLevel() ? $type : $type->getParent();

        $destination->topLevelTypeName = $rootType->getName();
        $destination->topLevelTypeSlug = $rootType->getSlug();
        $destination->typeIsAsset = $type->isAsset();
        $destination->typeSlug = $type->getSlug();
        $destination->typeName = $type->getName();
        $destination->assetName = $position->getAsset()->getName();
        $destination->assetId = strval($position->getAsset()->getId());
        $destination->positionId = strval($position->getId());
        $destination->value = !is_null($source->getValue()) ? $this->formatBase($source->getValue()->amount) : null;
        $destination->quantity = !is_null($source->getValue()) ? $source->getValue()->quantity : null;
        $destination->valueDate = $source->date->format('Y-m-d');

        return $destination;
    }
}
