<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Validator\Constraints;

use Groshy\Validator\Constraints\Money;
use Groshy\Validator\Constraints\MoneyValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class MoneyValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): MoneyValidator
    {
        return new MoneyValidator();
    }

    /**
     * @test
     */
    public function it_allows_null_as_valid_value()
    {
        $this->validator->validate(null, new Money());

        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function it_allows_empty_string_as_valid_value()
    {
        $this->validator->validate('', new Money());

        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function it_raises_unexpected_value_exception_for_non_string_values()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(new \stdClass(), new Money());
    }

    /**
     * @test
     * @dataProvider getValidMoney
     */
    public function it_passes_for_valid_money_values($amount)
    {
        $this->validator->validate($amount, new Money());

        $this->assertNoViolation();
    }

    public function getValidMoney()
    {
        return [
            ['10'],
            [20.00],
            ['9.99'],
        ];
    }

    /**
     * @test
     * @dataProvider getInvalidMoney
     */
    public function it_raises_violation_for_invalid_money_format($amount)
    {
        $constraint = new Money([
            'message' => 'myMessage',
        ]);

        $this->validator->validate($amount, $constraint);

        $this->buildViolation('myMessage')
            ->assertRaised();
    }

    public function getInvalidMoney()
    {
        return [
            ['example'],
            ['e.00'],
            ['.0e'],
            ['9.999'],
        ];
    }
}
