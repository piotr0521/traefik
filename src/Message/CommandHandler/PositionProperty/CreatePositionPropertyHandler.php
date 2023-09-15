<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionProperty;

use AutoMapperPlus\AutoMapperInterface;
use DateTime;
use Groshy\Entity\AssetProperty;
use Groshy\Entity\PositionProperty;
use Groshy\Enum\TransactionTypeKey;
use Groshy\Message\Command\PositionProperty\CreatePositionPropertyCommand;
use Groshy\Message\Command\Transaction\CreateTransactionListCommand;
use Groshy\Message\Dto\Transaction\CreateTransactionDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CreatePositionPropertyHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionPropertyManager,
        private readonly ManagerInterface $assetPropertyManager,
        // private readonly RepositoryInterface $transactionTypeRepository,
        private readonly RepositoryInterface $assetTypeRepository,
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreatePositionPropertyCommand $message): PositionProperty
    {
        /** @var AssetProperty $asset */
        $asset = $this->mapper->mapToObject($message->dto, $this->assetPropertyManager->create());
        $asset->setAssetType($this->assetTypeRepository->findOneBy(['name' => 'Investment Property']));
        /** @var PositionProperty $resource */
        $resource = $this->mapper->mapToObject($message->dto, $this->positionPropertyManager->create());
        $resource->setAsset($asset);
        $this->assetPropertyManager->update($asset);
        $this->positionPropertyManager->update($resource, true);

        $list = [];
        $dto = new CreateTransactionDto();
        $dto->position = $resource;
        $dto->amount = $message->dto->purchaseValue;
        $dto->type = $this->transactionTypeRepository->findOneBy(['shortName' => 'BUY']);
        $dto->transactionDate = $message->dto->purchaseDate;

        $list[] = $dto;
        $dto = clone $dto;
        $dto->transactionDate = new DateTime();
        $dto->type = $this->transactionTypeRepository->findOneBy(['shortName' => TransactionTypeKey::VALUE_UPDATE]);
        $dto->amount = $message->dto->currentValue;
        $list[] = $dto;

        $this->messageBus->dispatch(new CreateTransactionListCommand($list));

        return $resource;
    }
}
