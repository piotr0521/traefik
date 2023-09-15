<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\Metric;

enum CalculationLineEvent: string
{
    // money produced by the investment
    case START = 'start';

    // money produced by the investment
    case END = 'end';

    // money produced by the investment
    case MOVEMENT = 'movement';
}
