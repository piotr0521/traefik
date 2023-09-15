<?php

declare(strict_types=1);

namespace Groshy\Validator\Constraints;

use Groshy\Entity\Sponsor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Talav\Component\Resource\Repository\RepositoryInterface;

class DeletableSponsorValidator extends ConstraintValidator
{
    public function __construct(
        private readonly RepositoryInterface $assetRepository
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DeletableSponsor) {
            throw new UnexpectedTypeException($constraint, DeletableSponsor::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!($value instanceof Sponsor)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, Sponsor::class);
        }

        $count = $this->assetRepository->countBySponsor($value);
        if ($count > 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
