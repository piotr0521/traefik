<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCreditCard;

use Groshy\Entity\Institution;
use Money\Money;

class UpdatePositionCreditCardDto
{
    public ?Money $cardLimit = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    public ?string $name = null;

    public ?array $tags = null;
}
