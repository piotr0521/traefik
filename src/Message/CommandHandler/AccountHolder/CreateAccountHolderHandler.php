<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\AccountHolder;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\AccountHolder;
use Groshy\Message\Command\AccountHolder\CreateAccountHolderCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class CreateAccountHolderHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $accountHolderManager,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(CreateAccountHolderCommand $message): AccountHolder
    {
        /** @var AccountHolder $accountHolder */
        $accountHolder = $this->mapper->mapToObject($message->dto, $this->accountHolderManager->create());
        $this->accountHolderManager->update($accountHolder, true);

        return $accountHolder;
    }
}
