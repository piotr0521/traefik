<?php

declare(strict_types=1);

namespace Groshy\Domain\Enum;

enum Privacy: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';

    public static function choices(): array
    {
        return array_map(static fn (Privacy $privacy): string => $privacy->value, Privacy::cases());
    }
}
