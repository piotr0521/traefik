<?php

declare(strict_types=1);

namespace Groshy\Validator\Constraints;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exception\ParserException;
use Money\Parser\DecimalMoneyParser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MoneyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Money) {
            throw new UnexpectedTypeException($constraint, Money::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_scalar($value) && !$value instanceof \Stringable) {
            throw new UnexpectedValueException($value, 'string');
        }
        $value = strval($value);
        $fractions = explode('.', $value);
        if (isset($fractions[1]) && strlen($fractions[1]) > 2) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }

        try {
            (new DecimalMoneyParser(new ISOCurrencies()))->parse($value, new Currency('USD'));
        } catch (ParserException $e) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
