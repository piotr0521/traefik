<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionCreditCard;

use AutoMapperPlus\AutoMapperInterface;
use DateTime;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\Account;
use Groshy\Entity\PositionCreditCard;
use Groshy\Message\Command\PositionCreditCard\CreatePositionCreditCardCommand;
use Groshy\Message\Command\PositionEvent\CreatePositionEventCommand;
use Groshy\Message\Dto\PositionCreditCard\CreatePositionCreditCardDto;
use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\PositionValueDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CreatePositionCreditCardHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionCreditCardManager,
        private readonly ManagerInterface $accountManager,
        private readonly RepositoryInterface $liabilityCreditCardRepository,
        private readonly RepositoryInterface $accountTypeRepository,
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreatePositionCreditCardCommand $message): PositionCreditCard
    {
        /** @var PositionCreditCard $position */
        $position = $this->mapper->mapToObject($message->dto, $this->positionCreditCardManager->create());
        $position->setAsset($this->liabilityCreditCardRepository->getCreditCardLiability());
        $position->setAccount($this->getAccount($message->dto));
        $this->positionCreditCardManager->update($position, true);
        if (null !== $message->dto->balance) {
            $this->messageBus->dispatch(new CreatePositionEventCommand(CreatePositionEventDto::factory(
                date: new DateTime(),
                position: $position,
                value: PositionValueDto::factory(amount: $message->dto->balance),
                type: PositionEventType::BALANCE_UPDATE
            )));
        }

        return $position;
    }

    private function getAccount(CreatePositionCreditCardDto $dto): ?Account
    {
        $type = $this->accountTypeRepository->getCreditCardType();

        return $this->accountManager->getAccount($dto->createdBy, $dto->institution, $type, $dto->accountHolder, $dto->name);
    }
}
