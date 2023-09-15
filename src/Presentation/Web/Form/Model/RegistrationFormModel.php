<?php

declare(strict_types=1);

namespace Groshy\Presentation\Web\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Talav\UserBundle\Validator\Constraints\RegisteredUser;

final class RegistrationFormModel
{
    /**
     * @Assert\NotBlank(message="talav.email.blank")
     * @Assert\Email(message="talav.email.invalid", mode="strict")
     * @RegisteredUser(message="talav.email.already_used")
     */
    public ?string $email = null;

    /**
     * @Assert\NotBlank(message="talav.username.blank")
     * @RegisteredUser(message="talav.username.already_used", field="username")
     */
    public ?string $username = null;

    /**
     * @Assert\NotBlank
     */
    public ?string $firstName = null;

    /**
     * @Assert\NotBlank
     */
    public ?string $lastName = null;

    /**
     * @Assert\NotBlank(message="talav.password.blank")
     * @Assert\Length(min=4, minMessage="talav.password.short", max=254, maxMessage="talav.password.long")
     */
    public ?string $password = null;
}
