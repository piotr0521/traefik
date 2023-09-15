<?php

declare(strict_types=1);

namespace Groshy\Domain\Enum;

enum AccountSync: string
{
    case MANUAL = 'Manual';

    // Item can be updated in the background
    case PLAID_AUTO = 'Plaid Auto';

    // Item requires user interaction to be updated
    case PLAID_MANUAL = 'Plaid Manual';

    public static function choices(): array
    {
        return array_map(static fn (AccountSync $privacy): string => $privacy->value, AccountSync::cases());
    }
}
