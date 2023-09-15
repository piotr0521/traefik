<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\AccountHolder;

use Groshy\Entity\AccountHolder;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\User\Manager\UserManagerInterface;
use Talav\Component\User\Message\Event\NewUserEvent;
use Webmozart\Assert\Assert;

final class CreateAccountHolderForNewUserHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly UserManagerInterface $userManager,
        private readonly ManagerInterface $accountHolderManager
    ) {
    }

    public function __invoke(NewUserEvent $message): void
    {
        $user = $this->userManager->getRepository()->find($message->id);
        Assert::notNull($user);
        /** @var AccountHolder $accountHolder */
        $accountHolder = $this->accountHolderManager->create();
        $accountHolder->setName($user->getFirstName().' '.$user->getLastName());
        $accountHolder->setCreatedBy($user);
        $this->accountHolderManager->update($accountHolder, true);
    }
}
