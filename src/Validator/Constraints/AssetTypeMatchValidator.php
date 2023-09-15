<?php

declare(strict_types=1);

namespace Groshy\Validator\Constraints;

use Groshy\Config\ConfigProvider;
use Groshy\Entity\AssetType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AssetTypeMatchValidator extends ConstraintValidator
{
    public function __construct(private readonly ConfigProvider $provider)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AssetTypeMatch) {
            throw new UnexpectedTypeException($constraint, AssetTypeMatch::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!($value instanceof AssetType)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, AssetType::class);
        }
        $config = $this->provider->getConfig($value);
        if ($config->assetClass !== $constraint->assetClass) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
