<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionSecurity;

use Groshy\Entity\Tag;

class UpdatePositionSecurityDto
{
    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];
}
