<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\Metric;

use Webmozart\Assert\Assert;

final class XirrCalculator
{
    private NewtonRaphson $newton;

    private const GUESS = 0.1;

    public function __construct(private array $lines)
    {
        /* @var array<CalculationLine> $lines */
        Assert::minCount($lines, 2);
        Assert::allIsInstanceOf($lines, CalculationLine::class);
        Assert::true(reset($lines)->isStart(), 'First element of the array should be a start event');
        Assert::true(end($lines)->isEnd(), 'Last element of the array should be an end event');
        $this->newton = new NewtonRaphson();
    }

    public function result(): string
    {
        list($values, $days) = $this->preparePayments();
        if (1 == max($days)) {
            return '';
        }

        $fx = function ($x) use ($days, $values) {
            $sum = 0;
            foreach ($days as $idx => $day) {
                $sum += $values[$idx] * pow(1 + $x, ($days[0] - $day) / 365);
            }

            return $sum;
        };

        $fdx = function ($x) use ($days, $values) {
            $sum = 0;
            foreach ($days as $idx => $day) {
                $sum += (1 / 365) * ($days[0] - $day) * $values[$idx] * pow(1 + $x, (($days[0] - $day) / 365) - 1);
            }

            return $sum;
        };

        return strval($this->newton->run($fx, $fdx, self::GUESS));
    }

    /**
     * Prepare payment data by separating dates from values and prefix the array with the principal.
     */
    private function preparePayments(): array
    {
        $first = reset($this->lines);
        $values = [$first->cashFlow()->getAmount()];
        $days = [1];
        $startDate = $first->getDate();

        for ($i = 1; $i < count($this->lines) - 1; ++$i) {
            $values[] = $this->lines[$i]->cashFlow()->getAmount();
            $days[] = 1 + $startDate->diff($this->lines[$i]->getDate())->days;
        }
        $last = end($this->lines);
        if (!$last->getAfter()->isZero()) {
            $values[] = $last->getAfter()->getAmount();
            $days[] = 1 + $startDate->diff($last->getDate())->days;
        }

        return [$values, $days];
    }
}
