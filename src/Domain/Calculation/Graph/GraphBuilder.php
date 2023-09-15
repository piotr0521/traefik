<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\Graph;

use Groshy\Domain\Calculation\ValueObject\DateRange;
use Groshy\Domain\Calculation\ValueObject\DateValueAmount;
use Groshy\Domain\Calculation\ValueObject\DateValueQuantity;
use Groshy\Domain\Calculation\ValueObject\PositionAggregator;
use Groshy\Domain\Calculation\ValueObject\ValueList;
use Groshy\Domain\Entity\PositionAwareInterface;
use Groshy\Entity\AssetPriceHistoryInterface;
use Groshy\Entity\Position;
use Groshy\Entity\PositionValue;
use Money\Money;
use Webmozart\Assert\Assert;

final class GraphBuilder
{
    private PositionAggregator $positionValues;
    private PositionAggregator $assetPrices;

    public function __construct(
        private readonly DateRange $range,
        /** @var array<Position> $positions */
        private readonly array $positions,
        /* @var array<PositionValue> $positionValues */
        array $positionValues,
        /* @var array<AssetPriceHistoryInterface> $assetPrices */
        array $assetPrices
    ) {
        Assert::allIsInstanceOf($positions, Position::class);
        Assert::allIsInstanceOf($positionValues, PositionValue::class);
        Assert::allIsInstanceOf($assetPrices, AssetPriceHistoryInterface::class);
        $this->positionValues = new PositionAggregator(
            fn (PositionAwareInterface $p) => strval($p->getPosition()->getId()),
            fn (Position $p) => strval($p->getId()),
            $positionValues
        );
        $this->assetPrices = new PositionAggregator(
            fn (AssetPriceHistoryInterface $p) => strval($p->getAsset()->getId()),
            fn (Position $p) => strval($p->getAsset()->getId()),
            $assetPrices
        );
    }

    public function build(): ValueList
    {
        $graph = (new RangeValueListBuilder($this->range))->setDefault(Money::USD(0))->build();
        foreach ($this->positions as $position) {
            if ($position->getAsset()->getAssetType()->isQuantity()) {
                $graph = $graph->add($this->buildForQuantityAsset($position));
            } else {
                if ($position->getAsset()->getAssetType()->isAsset()) {
                    $graph = $graph->add($this->buildForValueAsset($position));
                } else {
                    $graph = $graph->subtract($this->buildForValueAsset($position));
                }
            }
        }

        return $graph;
    }

    private function buildForValueAsset(Position $position): ValueList
    {
        return (new RangeValueListBuilder($this->range))
            ->setDefault(Money::USD(0))
            ->add(
                array_map(
                    fn (PositionValue $positionValue) => new DateValueAmount($positionValue->getDate(), $positionValue->getAmount()),
                    $this->positionValues->get($position)
                )
            )
            ->build();
    }

    private function buildForQuantityAsset(Position $position): ValueList
    {
        return $this->buildPriceHistory($position)->multiply($this->buildQuantityList($position));
    }

    private function buildPriceHistory(Position $position): ValueList
    {
        return (new RangeValueListBuilder($this->range))
            ->setDefault(Money::USD(0))
            ->add(
                array_map(
                    fn (AssetPriceHistoryInterface $el) => new DateValueAmount($el->getPricedAt(), $el->getPrice()),
                    $this->assetPrices->get($position)
                )
            )
            ->build();
    }

    private function buildQuantityList(Position $position): ValueList
    {
        return (new RangeValueListBuilder($this->range))
            ->setDefault('0')
            ->add(
                array_map(
                    fn (PositionValue $positionValue) => new DateValueQuantity($positionValue->getDate(), strval($positionValue->getQuantity())),
                    $this->positionValues->get($position)
                )
            )
            ->build();
    }
}
