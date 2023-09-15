<?php

declare(strict_types=1);

namespace Groshy\Provider;

use Groshy\Domain\Enum\Color;

final class DefaultTagProvider
{
    public function getTagsStructure(): array
    {
        return [
            [
                'name' => 'Risk Tolerance',
                'position' => 0,
                'tags' => [
                    [
                        'name' => 'Conservative',
                        'position' => 0,
                        'color' => Color::COLOR1,
                    ],
                    [
                        'name' => 'Moderate',
                        'position' => 1,
                        'color' => Color::COLOR2,
                    ],
                    [
                        'name' => 'Aggressive',
                        'position' => 2,
                        'color' => Color::COLOR3,
                    ],
                ],
            ],
            [
                'name' => 'Investment Horizon',
                'position' => 1,
                'tags' => [
                    [
                        'name' => 'Short-term',
                        'position' => 0,
                        'color' => Color::COLOR4,
                    ],
                    [
                        'name' => 'Long-term',
                        'position' => 1,
                        'color' => Color::COLOR5,
                    ],
                ],
            ],
        ];
    }
}
