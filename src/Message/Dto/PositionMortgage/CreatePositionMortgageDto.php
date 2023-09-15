<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionMortgage;

use DateTime;
use Groshy\Entity\Institution;
use Money\Money;
use Talav\Component\User\Model\UserInterface;

class CreatePositionMortgageDto
{
    public ?string $name = null;

    public ?int $terms = null;

    public ?float $interest = null;

    public ?DateTime $mortgageDate = null;

    public ?Money $mortgageAmount = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    public ?array $tags = null;

    public ?UserInterface $createdBy = null;
}
