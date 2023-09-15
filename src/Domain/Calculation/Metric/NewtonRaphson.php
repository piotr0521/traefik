<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\Metric;

/**
 * Newton-Raphsons method to do a numerical analysis to find the effective interest.
 *
 * {@link https://en.wikipedia.org/wiki/Newton%27s_method}
 */
final class NewtonRaphson
{
    private int $precision;

    /**
     * @param int $precision The number of decimals to care to calculate
     */
    public function __construct(int $precision = 14)
    {
        $this->precision = $precision;
    }

    public function run(callable $fx, callable $fdx, float $guess): float
    {
        $counter = 0;
        $errorLimit = pow(10, -1 * $this->precision);
        do {
            $previousValue = $guess;
            $guess = $previousValue - ($fx($guess) / $fdx($guess));
            ++$counter;
        } while (abs($guess - $previousValue) > $errorLimit && $counter < pow(10, 3));

        return $guess;
    }
}
