<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\TagGroup;

use Groshy\Presentation\Api\Dto\IdInjectable;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdateTagGroupDto implements IdInjectable
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Assert\LessThanOrEqual(value: 9999)]
    public ?int $position = null;

    #[Ignore]
    public ?UuidInterface $id = null;
}
