<?php

declare(strict_types=1);

namespace Groshy\Domain\Enum;

enum PositionEventType: string
{
    // Value update
    case VALUE_UPDATE = 'VALUE_UPDATE';

    // Balance update, the same as VALUE_UPDATE but for liability accounts
    case BALANCE_UPDATE = 'BALANCE_UPDATE';

    // Cash distribution
    case DISTRIBUTION = 'DISTRIBUTION';

    // Cash distribution and reinvestment
    case REINVEST = 'REINVEST';

    // Cash contribution
    case CONTRIBUTION = 'CONTRIBUTION';

    // Sell stock
    case SELL = 'SELL';

    // Complete investment
    case COMPLETE = 'COMPLETE';

    public static function choices(): array
    {
        return array_map(static fn (PositionEventType $color): string => $color->value, PositionEventType::cases());
    }
}
