<?php

declare(strict_types=1);

namespace Groshy\Domain\Enum;

enum Color: string
{
    case COLOR1 = '#a8a29e';
    case COLOR2 = '#f87171';
    case COLOR3 = '#fb923c';
    case COLOR4 = '#fbbf24';
    case COLOR5 = '#a3e635';
    case COLOR6 = '#4ade80';
    case COLOR7 = '#34d399';
    case COLOR8 = '#2dd4bf';
    case COLOR9 = '#38bdf8';
    case COLOR10 = '#60f5fa';
    case COLOR11 = '#818cf8';
    case COLOR12 = '#a78bfa';
    case COLOR13 = '#c084fc';
    case COLOR14 = '#e879f9';

    public static function choices(): array
    {
        return array_map(static fn (Color $color): string => $color->value, Color::cases());
    }
}
