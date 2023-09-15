<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PlaidConnection;

use Groshy\Entity\PlaidConnection;
use Groshy\Message\Command\PlaidConnection\CreateConnectionCommand;
use Groshy\Message\Command\PlaidConnection\UpdateAccountsCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;
use TomorrowIdeas\Plaid\Plaid;

final class CreateConnectionHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $plaidConnectionManager,
        private readonly RepositoryInterface $institutionRepository,
        private readonly RepositoryInterface $userRepository,
        private readonly Plaid $plaid,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(CreateConnectionCommand $message): PlaidConnection
    {
        /** @var PlaidConnection $connection */
        $connection = $this->plaidConnectionManager->create();
        $response = $this->plaid->items->exchangeToken($message->publicToken);
        $connection->setAccessToken($response->access_token);
        $connection->setId($response->item_id);

        $itemData = $this->plaid->items->get($response->access_token);
        $connection->setInstitution($this->institutionRepository->findOneBy(['plaidId' => $itemData->item->institution_id]));
        $connection->setCreatedBy($this->userRepository->find($message->userId));
        $this->plaidConnectionManager->update($connection, true);

        $this->bus->dispatch(new UpdateAccountsCommand($message->userId, $response->item_id));

        return $connection;
    }
}
