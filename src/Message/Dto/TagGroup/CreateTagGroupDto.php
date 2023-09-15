<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\TagGroup;

use Talav\Component\User\Model\UserInterface;

class CreateTagGroupDto
{
    public ?string $name = null;

    public ?int $position = null;

    public ?UserInterface $createdBy = null;
}
