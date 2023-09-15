<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCash;

use DateTime;
use Groshy\Entity\AccountHolder;
use Groshy\Entity\AccountType;
use Groshy\Entity\Institution;
use Money\Money;
use Talav\Component\User\Model\UserInterface;

class CreatePositionCashDto
{
    public ?AccountType $accountType = null;

    public ?Institution $institution = null;

    public ?AccountHolder $accountHolder = null;

    public ?string $notes = null;

    public ?string $name = null;

    public array $tags = [];

    public ?float $yield = null;

    public ?Money $balance = null;

    public ?DateTime $balanceDate = null;

    public ?UserInterface $createdBy = null;
}
