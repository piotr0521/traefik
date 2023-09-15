<?php

declare(strict_types=1);

namespace Groshy\Tests\Helper;

use DateTime;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\Asset;
use Groshy\Entity\AssetType;
use Groshy\Entity\Position;
use Groshy\Entity\PositionEvent;
use Groshy\Entity\PositionValue;
use Groshy\Entity\Transaction;
use Groshy\Model\PositionDate;
use Groshy\Model\PositionDateValue;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

// Class to build different models required for unit tests
trait ModelBuilder
{
    protected function buildPosition(
        ?UuidInterface $positionId = null,
        ?Asset $asset = null
    ): Position {
        if (is_null($positionId)) {
            $positionId = Uuid::uuid4();
        }
        $position = new Position();
        $position->setId($positionId);
        if (!is_null($asset)) {
            $position->setAsset($asset);
        }

        return $position;
    }

    protected function buildAssetType(
        ?UuidInterface $assetTypeId = null,
        ?string $name = null,
        bool $isAsset = true,
        bool $isQuantity = false,
    ): AssetType {
        if (is_null($assetTypeId)) {
            $assetTypeId = Uuid::uuid4();
        }
        if (is_null($name)) {
            $name = $this->getFaker()->text(20);
        }
        $assetType = new AssetType();
        $assetType->setId($assetTypeId);
        $assetType->setName($name);
        $assetType->setIsAsset($isAsset);
        $assetType->setIsQuantity($isQuantity);

        return $assetType;
    }

    protected function buildAsset(
        ?UuidInterface $assetId = null,
        ?AssetType $assetType = null,
    ): Asset {
        if (is_null($assetId)) {
            $assetId = Uuid::uuid4();
        }
        $asset = new Asset();
        $asset->setId($assetId);
        $asset->setAssetType($assetType);

        return $asset;
    }

    protected function buildPositionDate(
        ?Position $position = null,
        ?DateTime $date = null,
        array $transactions = [],
        ?PositionDateValue $value = null
    ): PositionDate {
        if (is_null($position)) {
            $position = $this->buildPosition();
        }
        if (is_null($date)) {
            $date = new DateTime();
        }

        return new PositionDate($position, $date, $transactions, $value);
    }

    protected function buildPositionEvent(
        ?Position $position = null,
        ?DateTime $date = null,
        ?Money $valueAmount = null,
        ?PositionEventType $type = null,
        ?string $notes = null,
        array $transactions = []
    ): PositionEvent {
        if (is_null($position)) {
            $position = $this->buildPosition();
        }
        if (is_null($date)) {
            $date = new DateTime();
        }
        if (is_null($valueAmount)) {
            $valueAmount = Money::USD(100);
        }
        if (is_null($type)) {
            $type = PositionEventType::VALUE_UPDATE;
        }

        $positionEvent = new PositionEvent();
        $positionEvent->setPosition($position);
        $positionEvent->setDate($date);
        $positionEvent->setType($type);
        $positionEvent->setNotes($notes);
        if (!is_null($valueAmount)) {
            $positionValue = $this->buildPositionValue(position: $position, positionEvent: $positionEvent, amount: $valueAmount, date: $date);
            $positionEvent->setValue($positionValue);
        }
        foreach ($transactions as $transaction) {
            $positionEvent->addTransaction($transaction);
        }

        return $positionEvent;
    }

    protected function buildPositionValue(
        Position $position,
        PositionEvent $positionEvent,
        ?Money $amount = null,
        ?DateTime $date = null,
    ) {
        if (is_null($amount)) {
            $amount = Money::USD(100);
        }
        if (is_null($date)) {
            $date = new DateTime();
        }
        $positionValue = new PositionValue();
        $positionValue->setAmount($amount);
        $positionValue->setDate($date);
        $positionValue->setPosition($position);
        $positionValue->setPositionEvent($positionEvent);

        return $positionValue;
    }

    protected function buildTransaction(
        ?Position $position = null,
        ?Money $amount = null,
        ?DateTime $date = null,
    ): Transaction {
        if (is_null($position)) {
            $position = $this->buildPosition();
        }
        if (is_null($amount)) {
            $amount = Money::USD(100);
        }
        if (is_null($date)) {
            $date = new DateTime();
        }
        $transaction = new Transaction();
        $transaction->setPosition($position);
        $transaction->setAmount($amount);
        $transaction->setTransactionDate($date);

        return $transaction;
    }

    protected function getFaker(): Generator
    {
        return FakerFactory::create();
    }
}
