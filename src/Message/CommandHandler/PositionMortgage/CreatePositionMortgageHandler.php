<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionMortgage;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\Account;
use Groshy\Entity\PositionMortgage;
use Groshy\Enum\TransactionTypeKey;
use Groshy\Message\Command\PositionMortgage\CreatePositionMortgageCommand;
use Groshy\Message\Command\Transaction\CreateTransactionCommand;
use Groshy\Message\Dto\PositionMortgage\CreatePositionMortgageDto;
use Groshy\Message\Dto\Transaction\CreateTransactionDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CreatePositionMortgageHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionMortgageManager,
        private readonly ManagerInterface $accountManager,
        private readonly RepositoryInterface $accountTypeRepository,
        // private readonly RepositoryInterface $transactionTypeRepository,
        private readonly RepositoryInterface $liabilityMortgageRepository,
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreatePositionMortgageCommand $message): PositionMortgage
    {
        /** @var PositionMortgage $resource */
        $resource = $this->mapper->mapToObject($message->dto, $this->positionMortgageManager->create());
        $resource->setAsset($this->liabilityMortgageRepository->getMortgageAsset());
        $resource->setAccount($this->getAccount($message->dto));
        $this->positionMortgageManager->update($resource, true);

        $dto = new CreateTransactionDto();
        $dto->position = $resource;
        $dto->amount = $message->dto->mortgageAmount;
        $dto->type = $this->transactionTypeRepository->findOneBy(['shortName' => TransactionTypeKey::DEPOSIT]);
        $dto->transactionDate = $message->dto->mortgageDate;

        $this->messageBus->dispatch(new CreateTransactionCommand($dto));

        return $resource;
    }

    private function getAccount(CreatePositionMortgageDto $dto): ?Account
    {
        $type = $this->accountTypeRepository->getMortgageType();

        return $this->accountManager->getAccount($dto->createdBy, $dto->institution, $type, $dto->name);
    }
}
