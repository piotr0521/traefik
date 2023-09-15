<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionCertificateDeposit;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\Account;
use Groshy\Entity\PositionCertificateDeposit;
use Groshy\Enum\TransactionTypeKey;
use Groshy\Message\Command\PositionCertificateDeposit\CreatePositionCertificateDepositCommand;
use Groshy\Message\Command\Transaction\CreateTransactionCommand;
use Groshy\Message\Dto\PositionCertificateDeposit\CreatePositionCertificateDepositDto;
use Groshy\Message\Dto\Transaction\CreateTransactionDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CreatePositionCertificateDepositHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionCertificateDepositManager,
        private readonly ManagerInterface $accountManager,
        // private readonly RepositoryInterface $transactionTypeRepository,
        private readonly RepositoryInterface $assetCertificateDepositRepository,
        private readonly RepositoryInterface $accountTypeRepository,
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreatePositionCertificateDepositCommand $message): PositionCertificateDeposit
    {
        $resource = $this->mapper->mapToObject($message->dto, $this->positionCertificateDepositManager->create());
        $resource->setAsset($this->assetCertificateDepositRepository->getCertificateDepositAsset());
        $resource->setAccount($this->getAccount($message->dto));
        $this->positionCertificateDepositManager->update($resource, true);

        $dto = new CreateTransactionDto();
        $dto->position = $resource;
        $dto->amount = $message->dto->depositValue;
        $dto->type = $this->transactionTypeRepository->findOneBy(['shortName' => TransactionTypeKey::DEPOSIT]);
        $dto->transactionDate = $message->dto->depositDate;

        $this->messageBus->dispatch(new CreateTransactionCommand($dto));

        return $resource;
    }

    private function getAccount(CreatePositionCertificateDepositDto $dto): ?Account
    {
        $type = $this->accountTypeRepository->getCdType();

        return $this->accountManager->getAccount($dto->createdBy, $dto->institution, $type, $dto->name);
    }
}
