<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\User;

class UpdateUserDto
{
    public ?string $firstName = null;

    public ?string $lastName = null;

    public ?string $username = null;
}
