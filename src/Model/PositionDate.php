<?php

declare(strict_types=1);

namespace Groshy\Model;

use DateTime;
use Groshy\Entity\Position;
use Groshy\Entity\Transaction;
use Money\Money;
use Webmozart\Assert\Assert;

// Represents daily transactions and value for one position
final class PositionDate
{
    /** @var array<Transaction> */
    private array $transactions = [];

    private ?PositionDateValue $value;

    private ?PositionDateValue $previousValue;

    public function __construct(
        public readonly Position $position,
        public readonly DateTime $date,
        array $transactions = [],
        ?PositionDateValue $value = null,
        ?PositionDateValue $previousValue = null,
    ) {
        // all transactions belong for this position and have correct date
        foreach ($transactions as $transaction) {
            Assert::eq($position, $transaction->getPosition());
            Assert::eq($date->format('Y-m-d'), $transaction->getTransactionDate()->format('Y-m-d'));
            $this->transactions[] = $transaction;
        }
        $this->value = $value;
        $this->previousValue = $previousValue;
    }

    public function getValue(): ?PositionDateValue
    {
        return $this->value;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function hasValue(): bool
    {
        return !is_null($this->value);
    }

    public function getPreviousValue(): ?PositionDateValue
    {
        return $this->previousValue;
    }

    public function hasData(): bool
    {
        return count($this->transactions) > 0;
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function sumAmountByCallback(callable $fn): Money
    {
        return Money::sum(Money::USD(0), ...array_map(
            fn (Transaction $t) => $t->getAmount(),
            array_filter($this->transactions, fn (Transaction $t) => !is_null($t->getAmount()) && $fn($t))
        ));
    }

    public function sumQuantityByCallback(callable $fn): float
    {
        return array_sum(array_map(
            fn (Transaction $t) => floatval($t->getQuantity()),
            array_filter($this->transactions, fn (Transaction $t) => !is_null($t->getQuantity()) && $fn($t))
        ));
    }

    public function isZero(): bool
    {
        if (is_null($this->value) || is_null($this->value->getAmount())) {
            return true;
        }

        return $this->value->getAmount()->isZero();
    }
}
