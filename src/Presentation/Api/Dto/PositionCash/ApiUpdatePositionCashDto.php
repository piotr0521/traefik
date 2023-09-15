<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionCash;

use Groshy\Entity\Tag;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdatePositionCashDto
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    public ?float $yield = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public ?array $tags = null;
}
