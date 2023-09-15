<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Entity;

use Groshy\Entity\Transaction;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class TransactionTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_null_if_amount_is_not_set()
    {
        $transaction = new Transaction();
        self::assertNull($transaction->getAmount());
    }

    /**
     * @test
     */
    public function it_sets_amount_as_object()
    {
        $transaction = new Transaction();
        $transaction->setAmount(new Money(200, new Currency('USD')));
        self::assertEquals(200, $transaction->getAmount()->getAmount());
    }

    /**
     * @test
     */
    public function it_sets_amount_in_cents()
    {
        $transaction = new Transaction();
        $transaction->setAmountMinorUnit(200);
        self::assertEquals(200, $transaction->getAmount()->getAmount());
    }

    /**
     * @test
     */
    public function it_returns_structure_with_amount()
    {
        $transaction = new Transaction();
        $transaction->setAmountMinorUnit(234);
        self::assertEquals([
            'base' => '2.34',
            'minor' => '234',
            'currency' => 'USD',
        ], $transaction->getAmountStruct());
    }
}
