<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionSecurity;

use AutoMapperPlus\AutoMapperInterface;
use DateTime;
use Groshy\Entity\PositionSecurity;
use Groshy\Message\Command\AssetSecurityPrice\DownloadHistoryCommand;
use Groshy\Message\Command\PositionSecurity\CreatePositionSecurityCommand;
use Groshy\Message\Command\Transaction\CreateTransactionListCommand;
use Groshy\Message\Dto\Transaction\CreateTransactionDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CreatePositionSecurityHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionSecurityManager,
        // private readonly RepositoryInterface $transactionTypeRepository,
        private readonly RepositoryInterface $assetSecurityPriceRepository,
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreatePositionSecurityCommand $message): PositionSecurity
    {
        $resource = $this->mapper->mapToObject($message->dto, $this->positionSecurityManager->create());
        $this->positionSecurityManager->update($resource, true);

        $dto = new CreateTransactionDto();
        $dto->position = $resource;
        $dto->amount = $message->dto->averagePrice->multiply(strval($message->dto->quantity));
        $dto->quantity = $message->dto->quantity;
        $dto->transactionDate = $message->dto->purchaseDate;
        $dto->type = $this->transactionTypeRepository->findOneBy(['shortName' => 'BUY']);

        $this->messageBus->dispatch(new CreateTransactionListCommand([$dto]));

        $lastPrices = $this->assetSecurityPriceRepository->findBy(['asset' => $message->dto->asset], ['pricedAt' => 'DESC'], 1);
        if (0 == count($lastPrices) || (new DateTime('now'))->diff($lastPrices[0]->getPricedAt())->format('%a') > 5) {
            $this->messageBus->dispatch(new DownloadHistoryCommand($message->dto->asset->getSymbol()));
        }

        return $resource;
    }
}
