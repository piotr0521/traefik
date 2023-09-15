<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCreditCard;

use Groshy\Entity\AccountHolder;
use Groshy\Entity\Institution;
use Money\Money;
use Talav\Component\User\Model\UserInterface;

class CreatePositionCreditCardDto
{
    public ?Money $cardLimit = null;

    public ?Money $balance = null;

    public ?Institution $institution = null;

    public ?AccountHolder $accountHolder = null;

    public ?string $notes = null;

    public ?string $name = null;

    public ?array $tags = null;

    public ?UserInterface $createdBy = null;
}
