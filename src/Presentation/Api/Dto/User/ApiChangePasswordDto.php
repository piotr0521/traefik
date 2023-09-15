<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\User;

use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

class ApiChangePasswordDto
{
    #[UserPassword]
    public ?string $currentPassword = null;

    #[Assert\NotBlank(message: 'talav.password.blank')]
     #[Assert\Length(min: 4, max: 254, minMessage: 'talav.password.short', maxMessage: 'talav.password.long')]
    public ?string $newPassword = null;
}
