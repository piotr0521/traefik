<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionCrypto;

use AutoMapperPlus\AutoMapperInterface;
use DateTime;
use Groshy\Entity\PositionCrypto;
use Groshy\Message\Command\AssetCryptoPrice\DownloadHistoryCommand;
use Groshy\Message\Command\PositionCrypto\CreatePositionCryptoCommand;
use Groshy\Message\Command\Transaction\CreateTransactionListCommand;
use Groshy\Message\Dto\Transaction\CreateTransactionDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CreatePositionCryptoHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionCryptoManager,
        // private readonly RepositoryInterface $transactionTypeRepository,
        private readonly RepositoryInterface $assetCryptoPriceRepository,
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreatePositionCryptoCommand $message): PositionCrypto
    {
        $resource = $this->mapper->mapToObject($message->dto, $this->positionCryptoManager->create());
        $this->positionCryptoManager->update($resource, true);

        $dto = new CreateTransactionDto();
        $dto->position = $resource;
        $dto->amount = $message->dto->averagePrice->multiply(strval($message->dto->quantity));
        $dto->quantity = $message->dto->quantity;
        $dto->transactionDate = $message->dto->purchaseDate;
        $dto->type = $this->transactionTypeRepository->findOneBy(['shortName' => 'BUY']);

        $this->messageBus->dispatch(new CreateTransactionListCommand([$dto]));

        $lastPrices = $this->assetCryptoPriceRepository->findBy(['asset' => $message->dto->asset], ['pricedAt' => 'DESC'], 1);
        if (0 == count($lastPrices) || (new DateTime('now'))->diff($lastPrices[0]->getPricedAt())->format('%a') > 5) {
            $this->messageBus->dispatch(new DownloadHistoryCommand($message->dto->asset->getSymbol()));
        }

        return $resource;
    }
}
