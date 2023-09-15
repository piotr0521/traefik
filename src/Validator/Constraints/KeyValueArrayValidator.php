<?php

declare(strict_types=1);

namespace Groshy\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class KeyValueArrayValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof KeyValueArray) {
            throw new UnexpectedTypeException($constraint, KeyValueArray::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_array($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'array');
        }

        // make sure that only allowed keys here, this should be integrated with the form
        foreach ($value as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                $this->context->buildViolation($constraint->message)->addViolation();

                return;
            }
        }
    }
}
