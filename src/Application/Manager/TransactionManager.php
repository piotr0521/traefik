<?php

declare(strict_types=1);

namespace Groshy\Application\Manager;

use Groshy\Entity\Transaction;
use Groshy\Manager\TransactionType;
use Talav\Component\Resource\Manager\ResourceManager;

// use Groshy\Entity\TransactionType;

class TransactionManager extends ResourceManager
{
    public function copy(Transaction $transaction, TransactionType $type): Transaction
    {
        /** @var Transaction $copy */
        $copy = $this->create();
        $copy->setType($type);
        $copy->setAmount($transaction->getAmount());
        $copy->setTransactionDate($transaction->getTransactionDate());
        $copy->setPosition($transaction->getPosition());

        return $copy;
    }
}
