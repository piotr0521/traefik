<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCollectable;

use Talav\Component\User\Model\UserInterface;

class CreatePositionCollectableDto
{
    public ?string $notes = null;

    public ?string $name = null;

    public ?array $tags = null;

    public ?UserInterface $createdBy = null;
}
