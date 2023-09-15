<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCollectable;

class UpdatePositionCollectableDto
{
    public ?string $notes = null;

    public ?string $name = null;

    public ?array $tags = null;
}
