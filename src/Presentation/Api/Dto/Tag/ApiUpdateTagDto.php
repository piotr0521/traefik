<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\Tag;

use Groshy\Domain\Enum\Color;
use Groshy\Entity\TagGroup;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdateTagDto
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Assert\LessThanOrEqual(value: 9999)]
    public ?int $position = null;

    public ?TagGroup $tagGroup = null;

    #[Assert\Choice(callback: [Color::class, 'choices'])]
    public ?string $color = null;
}
