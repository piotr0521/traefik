<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionEvent;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\PositionEvent;
use Groshy\Entity\PositionValue;
use Groshy\Entity\Transaction;
use Groshy\Message\Command\Position\CalculatePositionListCommand;
use Groshy\Message\Command\PositionEvent\CreatePositionEventCommand;
use Groshy\Message\Command\PositionEvent\CreatePositionEventListCommand;
use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\CreateTransactionDto;
use Groshy\Message\Dto\PositionEvent\PositionValueDto;
use Money\Money;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class CreatePositionEventHandler implements MessageSubscriberInterface
{
    public function __construct(
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
        private readonly ManagerInterface $transactionManager,
        private readonly ManagerInterface $positionEventManager,
        private readonly ManagerInterface $positionValueManager,
    ) {
    }

    public static function getHandledMessages(): iterable
    {
        // handle this message on __invoke
        yield CreatePositionEventCommand::class;

        // also handle this message on handleOtherSmsNotification
        yield CreatePositionEventListCommand::class => [
            'method' => 'handleList',
        ];
    }

    /**
     * @return PositionEvent[]
     */
    public function handleList(CreatePositionEventListCommand $message): iterable
    {
        return $this->process($message->dtoList);
    }

    public function __invoke(CreatePositionEventCommand $message): PositionEvent
    {
        return $this->process([$message->dto])[0];
    }

    /**
     * @return PositionEvent[]
     */
    private function process(array $dtoList): array
    {
        $result = [];
        $positions = [];
        /** @var CreatePositionEventDto $dto */
        foreach ($dtoList as $dto) {
            $result[] = $this->createPositionEvent($dto);
            $positions[strval($dto->position)] = $dto->position;
        }
        $this->positionEventManager->flush();
        $this->messageBus->dispatch(new CalculatePositionListCommand(array_values($positions)));

        return $result;
    }

    private function createPositionEvent(CreatePositionEventDto $dto): PositionEvent
    {
        /** @var PositionEvent $event */
        $event = $this->positionEventManager->create();
        // manually map properties, automatic does not work, and it looks like an overkill to use mapper here
        $event->setPosition($dto->position);
        $event->setDate($dto->date);
        $event->setType($dto->type);
        $event->setNotes($dto->notes);

        // we are trying to complete the investment
        if (PositionEventType::COMPLETE == $event->getType()) {
            $dto->value = PositionValueDto::factory(Money::USD(0));
        }

        /** @var PositionValue $positionValue */
        $positionValue = $this->createPositionValue($dto->value);
        if (!is_null($positionValue)) {
            $positionValue->setDate($event->getDate());
            $positionValue->setPosition($event->getPosition());
            $event->setValue($positionValue);
        }
        foreach ($dto->transactions as $transactionData) {
            if (is_null($transactionData->amount) || $transactionData->amount->isZero()) {
                continue;
            }
            $transaction = $this->createTransaction($transactionData);
            $transaction->setTransactionDate($event->getDate());
            $transaction->setPosition($event->getPosition());
            $event->addTransaction($transaction);
        }
        $this->positionEventManager->update($event);

        return $event;
    }

    private function createPositionValue(?PositionValueDto $dto = null): ?PositionValue
    {
        if (is_null($dto)) {
            return null;
        }
        $value = $this->mapper->mapToObject($dto, $this->positionValueManager->create());
        $this->positionValueManager->update($value);

        return $value;
    }

    private function createTransaction(CreateTransactionDto $transactionData): Transaction
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionManager->create();
        if (!is_null($transactionData->amount)) {
            $transaction->setAmount($transactionData->amount);
        }
        if (!is_null($transactionData->quantity)) {
            $transaction->setQuantity($transactionData->quantity);
        }
        $this->transactionManager->update($transaction);

        return $transaction;
    }
}
