<?php

declare(strict_types=1);

namespace Groshy\Message\Command\User;

use Groshy\Entity\User;
use Groshy\Message\Dto\User\UpdateUserDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdateUserCommand implements DomainEventInterface
{
    public function __construct(
        public User $user,
        public UpdateUserDto $dto
    ) {
    }
}
