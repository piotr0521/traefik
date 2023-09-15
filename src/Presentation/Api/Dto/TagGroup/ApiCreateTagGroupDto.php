<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\TagGroup;

use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreateTagGroupDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Assert\LessThanOrEqual(value: 9999)]
    public int $position = 0;

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
