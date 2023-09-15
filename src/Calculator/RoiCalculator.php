<?php

declare(strict_types=1);

namespace Groshy\Calculator;

use Groshy\Model\PositionDateCollection;

final class RoiCalculator
{
    private array $cache = [];

    public function __construct(private PositionDateCollection $collection)
    {
    }

    public function result(): array
    {
        if (0 == count($this->cache)) {
            $this->cache = $this->calculate();
        }

        return $this->cache;
    }

    private function calculate(): array
    {
        $range = $this->collection->getFullRange();
        $startValue = $this->collection->getPositionDateSet($range->getStart())->getAmount();
        $endValue = $this->collection->getPositionDateSet($range->getEnd())->getAmount();
        $contribution = $this->collection->getContributions();
        $distribution = $this->collection->getDistributions();
        $in = $startValue->add($contribution);

        return [
            'percent' => $in->isZero() ? null : $endValue->add($distribution)->subtract($in)->ratioOf($in),
            'amount' => $endValue->add($distribution)->subtract($in),
        ];
    }
}
