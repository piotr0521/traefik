<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionBusiness;

class UpdatePositionBusinessDto
{
    public ?string $name = null;

    public ?string $description = null;

    public ?string $website = null;

    public ?float $ownership = null;

    public ?string $notes = null;

    public array $tags = [];
}
