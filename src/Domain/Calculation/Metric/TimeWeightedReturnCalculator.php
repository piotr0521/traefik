<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\Metric;

use Webmozart\Assert\Assert;

/**
 * Calculation based on https://www.fool.com/about/how-to-calculate-investment-returns/.
 */
final class TimeWeightedReturnCalculator
{
    private ?string $cache = null;

    public function __construct(private readonly array $lines)
    {
        /* @var array<CalculationLine> $lines */
        Assert::minCount($lines, 2);
        Assert::allIsInstanceOf($lines, CalculationLine::class);
        Assert::true(reset($lines)->isStart(), 'First element of the array should be a start event');
        Assert::true(end($lines)->isEnd(), 'Last element of the array should be an end event');
        bcscale(14);
    }

    public function result(): string
    {
        if (is_null($this->cache)) {
            $this->cache = $this->calculate();
        }

        return $this->cache;
    }

    private function calculate(): string
    {
        $result = '1';
        $lineReturns = array_filter(array_map(fn (CalculationLine $line) => $line->getRateOfReturn(), $this->lines), fn ($el) => !is_null($el));
        foreach ($lineReturns as $lineReturn) {
            $result = bcmul($result, $lineReturn);
        }

        return bcadd($result, '-1');
    }
}
