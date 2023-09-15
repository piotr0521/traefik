<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionMortgage;

use Groshy\Entity\Institution;
use Groshy\Entity\Tag;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdatePositionMortgageDto
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\GreaterThan(1)]
    #[Assert\LessThan(60)]
    public ?int $terms = null;

    #[Assert\GreaterThan(1)]
    public ?int $interest = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];
}
