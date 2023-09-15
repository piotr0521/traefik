<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionMortgage;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\Account;
use Groshy\Entity\PositionMortgage;
use Groshy\Message\Command\PositionMortgage\UpdatePositionMortgageCommand;
use Groshy\Message\Dto\PositionMortgage\UpdatePositionMortgageDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Talav\Component\User\Model\UserInterface;

final class UpdatePositionMortgageHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionMortgageManager,
        private readonly ManagerInterface $accountManager,
        private readonly RepositoryInterface $accountTypeRepository,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdatePositionMortgageCommand $message): PositionMortgage
    {
        $position = $message->resource;
        $this->mapper->mapToObject($message->dto, $position);
        $position->setAccount($this->getAccount($message->dto, $position->getCreatedBy()));
        $this->positionMortgageManager->update($position, true);

        return $position;
    }

    private function getAccount(UpdatePositionMortgageDto $dto, UserInterface $user): ?Account
    {
        $type = $this->accountTypeRepository->getMortgageType();

        return $this->accountManager->getAccount($user, $dto->institution, $type, $dto->name);
    }
}
