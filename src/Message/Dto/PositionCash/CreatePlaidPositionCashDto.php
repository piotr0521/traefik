<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCash;

use Groshy\Entity\Account;
use Talav\Component\User\Model\UserInterface;

class CreatePlaidPositionCashDto
{
    public ?string $name = null;

    public ?UserInterface $createdBy = null;

    public ?Account $account = null;
}
