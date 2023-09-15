<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionLoan;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\Account;
use Groshy\Entity\PositionLoan;
use Groshy\Message\Command\PositionLoan\UpdatePositionLoanCommand;
use Groshy\Message\Dto\PositionLoan\UpdatePositionLoanDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Talav\Component\User\Model\UserInterface;

final class UpdatePositionLoanHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionLoanManager,
        private readonly ManagerInterface $accountManager,
        private readonly RepositoryInterface $accountTypeRepository,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdatePositionLoanCommand $message): PositionLoan
    {
        $position = $message->resource;
        $this->mapper->mapToObject($message->dto, $position);
        $position->setAccount($this->getAccount($message->dto, $position->getCreatedBy()));
        $this->positionLoanManager->update($position, true);

        return $position;
    }

    private function getAccount(UpdatePositionLoanDto $dto, UserInterface $user): ?Account
    {
        $type = $this->accountTypeRepository->getLoanType();

        return $this->accountManager->getAccount($user, $dto->institution, $type, $dto->name);
    }
}
