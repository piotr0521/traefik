<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\AccountHolder;

use Groshy\Message\Command\AccountHolder\DeleteAccountHolderCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class DeleteAccountHolderHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $accountHolderManager,
    ) {
    }

    public function __invoke(DeleteAccountHolderCommand $message): void
    {
        $this->accountHolderManager->remove($message->resource);
        $this->accountHolderManager->flush();
    }
}
