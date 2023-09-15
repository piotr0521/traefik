<?php

declare(strict_types=1);

namespace Groshy\Validator\Constraints;

use Groshy\Config\ConfigProvider;
use Groshy\Entity\Position;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionEventTypeAllowedValidator extends ConstraintValidator
{
    public function __construct(
        private readonly RepositoryInterface $positionEventRepository,
        private readonly ConfigProvider $provider,
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PositionEventTypeAllowed) {
            throw new UnexpectedTypeException($constraint, PositionEventTypeAllowed::class);
        }

        if (!isset($constraint->fieldMap['type'])) {
            throw new ConstraintDefinitionException('Field map should have a key for transaction type');
        }
        if (is_null($value->{$constraint->fieldMap['type']})) {
            return;
        }
        if (!isset($constraint->fieldMap['id']) && !isset($constraint->fieldMap['position'])) {
            throw new ConstraintDefinitionException('Either id or position fields should be set in the map');
        }
        if (isset($constraint->fieldMap['id']) && is_null($value->{$constraint->fieldMap['id']})) {
            return;
        }
        if (isset($constraint->fieldMap['position']) && is_null($value->{$constraint->fieldMap['position']})) {
            return;
        }
        /** @var ?Position $position */
        $position = null;
        $type = $value->{$constraint->fieldMap['type']};
        if (isset($constraint->fieldMap['id'])) {
            $position = $this->positionEventRepository->find($value->{$constraint->fieldMap['id']})->getPosition();
        }
        if (isset($constraint->fieldMap['position']) && !is_null($value->{$constraint->fieldMap['position']})) {
            $position = $value->{$constraint->fieldMap['position']};
        }

        $config = $this->provider->getConfig($position->getAsset()->getAssetType());
        if (!in_array($type, $config->positionEventTypes)) {
            $this->context->buildViolation($constraint->message)->atPath($constraint->fieldMap['type'])->addViolation();
        }
    }
}
