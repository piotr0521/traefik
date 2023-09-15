<?php

declare(strict_types=1);

namespace Groshy\Model;

use ArrayIterator;
use Traversable;

final class Dashboard
{
    private array $collection = [];

    public static function toDashData(array $values): array
    {
        $dash = new Dashboard();
        $iterator = new ArrayIterator($values);
        $iterator->uasort(function ($first, $second) {
            return $first->valueDate > $second->valueDate ? 1 : -1;
        });

        $dash->addValues($iterator);

        return $dash->buildDash();
    }

    private function addValues(Traversable $values): void
    {
        foreach ($values as $value) {
            $this->addValue($value);
        }
    }

    private function addValue(DashboardValue $value): void
    {
        $change = $this->calculateChange($value);
        foreach (AttributeModel::factory($value) as $model) {
            if ($model->isTotal()) {
                $this->getAttributeTracker($model)->add($value->valueDate, $value->typeIsAsset ? $change : -$change);
            } else {
                $this->getAttributeTracker($model)->add($value->valueDate, $change);
            }
        }
    }

    private function buildDash(): array
    {
        $totalAssets = $this->getAttributeTracker(AttributeModel::forBalance(true))->getValue()['graph'];
        $totalLiabilities = $this->getAttributeTracker(AttributeModel::forBalance(false))->getValue()['graph'];
        $this->getAttributeTracker(AttributeModel::total());
        foreach ($this->collection as $trackers) {
            foreach ($trackers as $tracker) {
                if ($tracker->getModel()->isAsset) {
                    $tracker->build($totalAssets);
                } else {
                    $tracker->build($totalLiabilities);
                }
            }
        }
        $result = [];
        foreach ($this->collection as $key => $trackers) {
            $result[$key] = array_map(function ($el) { return $el->getData(); }, $trackers);
        }

        return $result;
    }

    private function getAttributeTracker(AttributeModel $model): AttributeTracker
    {
        if (!isset($this->collection[$model->type])) {
            $this->collection[$model->type] = [];
        }
        if (!isset($this->collection[$model->type][$model->id])) {
            $this->collection[$model->type][$model->id] = new AttributeTracker($model);
        }

        return $this->collection[$model->type][$model->id];
    }

    private function calculateChange(DashboardValue $value): float
    {
        return $value->value - $this->getAttributeTracker(AttributeModel::forPosition($value))->getValue()['current'];
    }
}
