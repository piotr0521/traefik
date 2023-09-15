<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionCertificateDeposit;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\Account;
use Groshy\Entity\PositionCertificateDeposit;
use Groshy\Entity\User;
use Groshy\Message\Command\PositionCertificateDeposit\UpdatePositionCertificateDepositCommand;
use Groshy\Message\Dto\PositionCertificateDeposit\UpdatePositionCertificateDepositDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class UpdatePositionCertificateDepositHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionCertificateDepositManager,
        private readonly ManagerInterface $accountManager,
        private readonly RepositoryInterface $accountTypeRepository,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdatePositionCertificateDepositCommand $message): PositionCertificateDeposit
    {
        $position = $message->resource;
        $this->mapper->mapToObject($message->dto, $position);
        $position->setAccount($this->getAccount($message->dto, $position->getCreatedBy()));
        $this->positionCertificateDepositManager->update($position, true);

        return $position;
    }

    private function getAccount(UpdatePositionCertificateDepositDto $dto, User $user): ?Account
    {
        $type = $this->accountTypeRepository->getCdType();

        return $this->accountManager->getAccount($user, $dto->institution, $type, $dto->name);
    }
}
