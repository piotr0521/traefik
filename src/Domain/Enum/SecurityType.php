<?php

declare(strict_types=1);

namespace Groshy\Domain\Enum;

enum SecurityType: string
{
    case STOCK = 'Stock';
    case ETF = 'ETF';
    case MUTUAL_FUND = 'Mutual Fund';
    case BOND = 'Bond';

    public static function choices(): array
    {
        return array_map(static fn (SecurityType $security): string => $security->value, SecurityType::cases());
    }
}
