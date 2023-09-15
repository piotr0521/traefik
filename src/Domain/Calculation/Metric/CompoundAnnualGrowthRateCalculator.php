<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\Metric;

use Groshy\Model\DateRange;
use Webmozart\Assert\Assert;

final class CompoundAnnualGrowthRateCalculator
{
    private ?string $cache = null;

    public function __construct(private readonly TimeWeightedReturnCalculator $twrCalculator, private readonly DateRange $range)
    {
        Assert::true($range->diff()->days >= 365);
        bcscale(14);
    }

    public function result(): string
    {
        if (is_null($this->cache)) {
            $this->cache = $this->calculate();
        }

        return $this->cache;
    }

    // from https://static.twentyoverten.com/59b00a0441a46f312d08c93d/dxSbxcj20w-/TWRR-Overview.pdf
    // https://www.fool.com/about/how-to-calculate-investment-returns/
    // (1 + TWR) ^ (1 / No. of years) – 1
    // x^y = exp(y*log(x))
    private function calculate(): string
    {
        $days = $this->range->diff()->days;
        $n1 = bcadd('1', $this->twrCalculator->result());
        $n2 = bcdiv('365', strval($days));

        return bcadd('0', strval(exp(floatval($n2) * log(floatval($n1))) - 1));
    }
}
