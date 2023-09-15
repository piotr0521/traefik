<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionEvent;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\PositionEvent;
use Groshy\Entity\PositionValue;
use Groshy\Entity\Transaction;
use Groshy\Message\Command\Position\CalculatePositionListCommand;
use Groshy\Message\Command\PositionEvent\UpdatePositionEventCommand;
use Groshy\Message\Dto\PositionEvent\PositionValueDto;
use Groshy\Message\Dto\PositionEvent\UpdatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\UpdateTransactionDto;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class UpdatePositionEventHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
        private readonly ManagerInterface $transactionManager,
        private readonly ManagerInterface $positionEventManager,
        private readonly ManagerInterface $positionValueManager,
    ) {
    }

    public function __invoke(UpdatePositionEventCommand $message): PositionEvent
    {
        $positionEvent = $this->updatePositionEvent($message->dto, $message->positionEvent);
        $this->positionEventManager->flush();
        $this->messageBus->dispatch(new CalculatePositionListCommand([$positionEvent->getPosition()]));

        return $positionEvent;
    }

    private function updatePositionEvent(UpdatePositionEventDto $dto, PositionEvent $positionEvent): PositionEvent
    {
        // manually map properties, automatic does not work, and it looks like an overkill to use mapper here
        if (!is_null($dto->date)) {
            $positionEvent->setDate($dto->date);
        }
        if (!is_null($dto->notes)) {
            $positionEvent->setNotes($dto->notes);
        }
        if (!is_null($dto->type)) {
            $positionEvent->setType($dto->type);
        }

        $positionValue = $this->updatePositionValue($dto->value, $positionEvent->getValue());
        if (!is_null($positionValue)) {
            $positionValue->setDate($positionEvent->getDate());
            $positionValue->setPosition($positionEvent->getPosition());
            $positionEvent->setValue($positionValue);
        }

        /** @var UpdateTransactionDto $transactionData */
        foreach ($dto->transactions as $transactionData) {
            if (!$transactionData->hasValue()) {
                $this->transactionManager->remove($transactionData->transaction);
            }
            $transaction = $this->updateTransaction($transactionData);
            $transaction->setTransactionDate($positionEvent->getDate());
            $transaction->setPosition($positionEvent->getPosition());
            $positionEvent->addTransaction($transaction);
        }
        $this->positionEventManager->update($positionEvent);

        return $positionEvent;
    }

    private function updatePositionValue(?PositionValueDto $dto = null, ?PositionValue $positionValue = null): ?PositionValue
    {
        if (is_null($dto)) {
            return $positionValue;
        }
        if (is_null($positionValue)) {
            $positionValue = $this->positionValueManager->create();
        }
        $value = $this->mapper->mapToObject($dto, $positionValue);
        $this->positionValueManager->update($value);

        return $value;
    }

    private function updateTransaction(UpdateTransactionDto $transactionData): Transaction
    {
        /* @var Transaction $transaction */
        if (is_null($transactionData->transaction)) {
            $transaction = $this->transactionManager->create();
        } else {
            $transaction = $transactionData->transaction;
        }
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
