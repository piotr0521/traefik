<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdateUserDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $firstName = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $lastName = null;

    #[Assert\NotBlank(message: 'talav.username.blank')]
    public ?string $username = null;
}
