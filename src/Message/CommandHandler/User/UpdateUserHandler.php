<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\User;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\User;
use Groshy\Message\Command\User\UpdateUserCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Talav\Component\User\Manager\UserManagerInterface;

#[AsMessageHandler]
final class UpdateUserHandler
{
    public function __construct(
        private readonly AutoMapperInterface $mapper,
        private readonly UserManagerInterface $userManager,
    ) {
    }

    public function __invoke(UpdateUserCommand $message): User
    {
        $this->mapper->mapToObject($message->dto, $message->user);
        $this->userManager->update($message->user, true);

        return $message->user;
    }
}
