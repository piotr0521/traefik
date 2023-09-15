<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionMortgage;

use Groshy\Entity\Institution;

class UpdatePositionMortgageDto
{
    public ?string $name = null;

    public ?int $terms = null;

    public ?float $interest = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    public ?array $tags = null;
}
