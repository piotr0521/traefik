<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionCreditCard;

use Groshy\Entity\Institution;
use Groshy\Entity\Tag;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdatePositionCreditCardDto
{
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\GreaterThan(1)]
    public ?string $cardLimit = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public ?array $tags = null;
}
