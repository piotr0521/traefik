<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\AccountHolder;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\AccountHolder;
use Groshy\Message\Command\AccountHolder\UpdateAccountHolderCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class UpdateAccountHolderHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $accountHolderManager,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdateAccountHolderCommand $message): AccountHolder
    {
        $accountHolder = $this->mapper->mapToObject($message->dto, $message->resource);
        $this->accountHolderManager->update($accountHolder, true);

        return $accountHolder;
    }
}
