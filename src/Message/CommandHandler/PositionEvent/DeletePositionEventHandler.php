<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionEvent;

use Groshy\Entity\PositionValue;
use Groshy\Message\Command\Position\CalculatePositionListCommand;
use Groshy\Message\Command\PositionEvent\DeletePositionEventCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class DeletePositionEventHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionEventManager,
        private readonly ManagerInterface $transactionManager,
        private readonly ManagerInterface $positionValueManager,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(DeletePositionEventCommand $message): void
    {
        $positionEvent = $message->positionEvent;
        $this->removePositionValue($positionEvent->getValue());
        $this->removeTransactions($positionEvent->getTransactions());
        $this->positionEventManager->remove($positionEvent);
        $this->positionEventManager->flush();

        $this->messageBus->dispatch(new CalculatePositionListCommand([$positionEvent->getPosition()]));
    }

    private function removePositionValue(?PositionValue $positionValue = null): void
    {
        if (is_null($positionValue)) {
            return;
        }
        $this->positionValueManager->remove($positionValue);
    }

    private function removeTransactions(iterable $transactions = []): void
    {
        foreach ($transactions as $transaction) {
            $this->transactionManager->remove($transaction);
        }
    }
}
