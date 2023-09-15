<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCash;

class UpdatePositionCashDto
{
    public ?string $notes = null;

    public ?string $name = null;

    public ?float $yield = null;

    public ?array $tags = null;
}
