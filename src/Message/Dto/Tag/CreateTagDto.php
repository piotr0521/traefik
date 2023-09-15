<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\Tag;

use Groshy\Domain\Enum\Color;
use Groshy\Entity\TagGroup;
use Talav\Component\User\Model\UserInterface;

class CreateTagDto
{
    public ?string $name = null;

    public ?int $position = null;

    public ?TagGroup $tagGroup = null;

    public ?Color $color = null;

    public ?UserInterface $createdBy = null;
}
