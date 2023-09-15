<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionSecurity;

use Groshy\Entity\Institution;
use Groshy\Entity\Tag;

class ApiUpdatePositionSecurityDto
{
    public ?Institution $institution = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];
}
