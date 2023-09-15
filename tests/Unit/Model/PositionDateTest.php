<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Model;

use DateTime;
use Groshy\Entity\Position;
use Groshy\Entity\Transaction;
use Groshy\Model\PositionDate;
use Groshy\Tests\Helper\ModelBuilder;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class PositionDateTest extends TestCase
{
    use ModelBuilder;

    /**
     * @test
     */
    public function it_calculates_sum_of_all_transaction_filtered_by_callback()
    {
        $position = new Position();
        $date = new DateTime();
        $transaction1 = new Transaction();
        $transaction1->setAmount(Money::USD(10));
        $transaction1->setTransactionDate($date);
        $transaction1->setPosition($position);
        $transaction2 = new Transaction();
        $transaction2->setAmount(Money::USD(20));
        $transaction2->setTransactionDate($date);
        $transaction2->setPosition($position);
        $transaction3 = new Transaction();
        $transaction3->setAmount(Money::USD(-20));
        $transaction3->setTransactionDate($date);
        $transaction3->setPosition($position);

        $positionDate = new PositionDate($position, new DateTime(), [$transaction1, $transaction2, $transaction3]);
        self::assertEquals('30', $positionDate->sumAmountByCallback(fn ($t) => $t->getAmount()->isPositive())->getAmount());
        self::assertEquals('20', $positionDate->sumAmountByCallback(fn (Transaction $t) => $t->getAmount()->greaterThan(Money::USD('11')))->getAmount());
    }
}
