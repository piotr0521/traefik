<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionLoan;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\Account;
use Groshy\Entity\PositionLoan;
use Groshy\Enum\TransactionTypeKey;
use Groshy\Message\Command\PositionLoan\CreatePositionLoanCommand;
use Groshy\Message\Command\Transaction\CreateTransactionCommand;
use Groshy\Message\Dto\PositionLoan\CreatePositionLoanDto;
use Groshy\Message\Dto\Transaction\CreateTransactionDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CreatePositionLoanHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionLoanManager,
        private readonly ManagerInterface $accountManager,
        private readonly RepositoryInterface $accountTypeRepository,
        // private readonly RepositoryInterface $transactionTypeRepository,
        private readonly RepositoryInterface $liabilityLoanRepository,
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreatePositionLoanCommand $message): PositionLoan
    {
        /** @var PositionLoan $resource */
        $resource = $this->mapper->mapToObject($message->dto, $this->positionLoanManager->create());
        $resource->setAsset($this->liabilityLoanRepository->getLoanAsset());
        $resource->setAccount($this->getAccount($message->dto));
        $this->positionLoanManager->update($resource, true);

        $dto = new CreateTransactionDto();
        $dto->position = $resource;
        $dto->amount = $message->dto->loanAmount;
        $dto->type = $this->transactionTypeRepository->findOneBy(['shortName' => TransactionTypeKey::DEPOSIT]);
        $dto->transactionDate = $message->dto->loanDate;

        $this->messageBus->dispatch(new CreateTransactionCommand($dto));

        return $resource;
    }

    private function getAccount(CreatePositionLoanDto $dto): ?Account
    {
        $type = $this->accountTypeRepository->getLoanType();

        return $this->accountManager->getAccount($dto->createdBy, $dto->institution, $type, $dto->name);
    }
}
