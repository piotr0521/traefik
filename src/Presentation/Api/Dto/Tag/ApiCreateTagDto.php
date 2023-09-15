<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\Tag;

use Groshy\Domain\Enum\Color;
use Groshy\Entity\TagGroup;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreateTagDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Assert\LessThanOrEqual(value: 9999)]
    public int $position = 0;

    #[Assert\NotBlank]
    public ?TagGroup $tagGroup = null;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [Color::class, 'choices'])]
    public ?string $color = null;

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
