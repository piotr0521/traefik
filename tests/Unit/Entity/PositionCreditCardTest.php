<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Entity;

use Groshy\Entity\PositionCreditCard;
use Groshy\Entity\PositionValue;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

final class PositionCreditCardTest extends TestCase
{
    /**
     * @test
     */
    public function it_correctly_calculates_card_utilization()
    {
        $value = new PositionValue();
        $value->setAmount(Money::USD(200000));
        $position = new PositionCreditCard();
        $position->setLastValue($value);
        $position->getData()->setCardLimit(new Money(400000, new Currency('USD')));
        self::assertEquals('0.5', $position->getUtilization());
    }
}
