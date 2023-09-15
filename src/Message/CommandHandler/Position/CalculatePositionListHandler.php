<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Position;

use DateTime;
use Groshy\Domain\Calculation\Metric\XirrCalculator;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\Position;
use Groshy\Entity\PositionEvent;
use Groshy\Entity\PositionValue;
use Groshy\Message\Command\Position\CalculatePositionListCommand;
use Groshy\Provider\PositionDateCollectionFactory;
use Money\Money;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CalculatePositionListHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly PositionDateCollectionFactory $collectionFactory,
        private readonly RepositoryInterface $positionValueRepository,
        private readonly RepositoryInterface $positionEventRepository,
        private readonly RepositoryInterface $transactionRepository,
        private readonly ManagerInterface $positionManager,
    ) {
    }

    public function __invoke(CalculatePositionListCommand $message): void
    {
        $this->updateLastValues($message->positions);
        foreach ($message->positions as $position) {
            $this->updateDistributionsAndContributions($position);
            $this->updateStartDate($position);
            $this->updateCompleteDate($position);
            $this->updateIrr($position);
            $this->updateMultiplier($position);
            $this->positionManager->update($position);
        }
        $this->positionManager->flush();
    }

    private function updateDistributionsAndContributions(Position $position)
    {
        $position->setDistributions(
            Money::USD($this->transactionRepository->sumPositive($position))
        );
        $position->setContributions(
            Money::USD($this->transactionRepository->sumNegative($position))->multiply(-1)
        );
    }

    public function updateLastValues(array $positions): void
    {
        $values = $this->positionValueRepository->findLastByPositionsAndBeforeDate(
            positions: $positions,
            before: new DateTime('+1 day')
        );
        /** @var PositionValue $positionValue */
        foreach ($values as $positionValue) {
            $positionValue->getPosition()->setLastValue($positionValue);
        }
    }

    private function updateStartDate(Position $position): void
    {
        $positionEvent = $this->positionEventRepository->getFirstForPosition($position);
        if (is_null($positionEvent)) {
            $position->setStartDate(null);
        } else {
            $position->setStartDate($positionEvent->getDate());
        }
    }

    private function updateCompleteDate(Position $position): void
    {
        /** @var PositionEvent $positionEvent */
        $positionEvent = $this->positionEventRepository->findOneBy(['position' => $position, 'type' => PositionEventType::COMPLETE]);
        if (is_null($positionEvent)) {
            $position->setCompleteDate(null);
        } else {
            $position->setCompleteDate($positionEvent->getDate());
        }
    }

    private function updateIrr(Position $position): void
    {
        if (is_null($position->getStartDate())) {
            return;
        }
        $to = !is_null($position->getCompleteDate()) ? $position->getCompleteDate() : new DateTime();
        $collection = $this->collectionFactory->build([$position], $position->getStartDate(), $to);
        if (is_null($collection)) {
            return;
        }
        $lines = $collection->getLineCollection()->toArray();
        $xirr = new XirrCalculator($lines);
        $position->setIrr($xirr->result());
    }

    private function updateMultiplier(Position $position): void
    {
        if (is_null($position->getLastValue()) || $position->getContributions()->isZero()) {
            $position->setMultiplier('1');

            return;
        }
        $position->setMultiplier($position->getDistributions()->add($position->getLastValue()->getAmount())->ratioOf($position->getContributions()));
    }
}
