<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\AccountHolder;

use Talav\Component\User\Model\UserInterface;

class CreateAccountHolderDto
{
    public ?string $name = null;

    public ?UserInterface $createdBy = null;
}
