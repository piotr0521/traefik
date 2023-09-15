<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionBusiness;

use AutoMapperPlus\AutoMapperInterface;
use DateTime;
use Groshy\Entity\AssetBusiness;
use Groshy\Entity\PositionBusiness;
use Groshy\Enum\TransactionTypeKey;
use Groshy\Message\Command\PositionBusiness\CreatePositionBusinessCommand;
use Groshy\Message\Command\Transaction\CreateTransactionListCommand;
use Groshy\Message\Dto\Transaction\CreateTransactionDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CreatePositionBusinessHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionBusinessManager,
        private readonly ManagerInterface $assetBusinessManager,
        // private readonly RepositoryInterface $transactionTypeRepository,
        private readonly RepositoryInterface $assetTypeRepository,
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreatePositionBusinessCommand $message): PositionBusiness
    {
        /** @var AssetBusiness $asset */
        $asset = $this->mapper->mapToObject($message->dto, $this->assetBusinessManager->create());
        $asset->setAssetType($this->assetTypeRepository->findOneBy(['name' => 'Private Business']));
        /** @var PositionBusiness $resource */
        $resource = $this->mapper->mapToObject($message->dto, $this->positionBusinessManager->create());
        $resource->setAsset($asset);
        $this->assetBusinessManager->update($asset);
        $this->positionBusinessManager->update($resource, true);

        $list = [];
        $dto = new CreateTransactionDto();
        $dto->position = $resource;
        $dto->amount = $message->dto->originalValue;
        $dto->type = $this->transactionTypeRepository->findOneBy(['shortName' => 'BUY']);
        $dto->transactionDate = $message->dto->originalDate;

        $list[] = $dto;
        $dto = clone $dto;
        $dto->transactionDate = new DateTime();
        $dto->type = $this->transactionTypeRepository->findOneBy(['shortName' => TransactionTypeKey::VALUE_UPDATE]);
        $dto->amount = $message->dto->currentValue;
        $dto->transactionDate = $message->dto->valueDate;
        $list[] = $dto;

        $this->messageBus->dispatch(new CreateTransactionListCommand($list));

        return $resource;
    }
}
