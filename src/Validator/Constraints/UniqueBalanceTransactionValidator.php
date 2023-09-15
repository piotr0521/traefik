<?php

declare(strict_types=1);

namespace Groshy\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Talav\Component\Resource\Repository\RepositoryInterface;

class UniqueBalanceTransactionValidator extends ConstraintValidator
{
    public function __construct(
        private readonly RepositoryInterface $transactionRepository,
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueBalanceTransaction) {
            throw new UnexpectedTypeException($constraint, UniqueBalanceTransaction::class);
        }

        if (!isset($constraint->fieldMap['transactionDate']) || !isset($constraint->fieldMap['type'])) {
            throw new ConstraintDefinitionException('Field map should have keys for date and type');
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
        $data = [
            'position' => null,
            'transactionDate' => null,
            'type' => null,
        ];
        $transaction = null;
        if (isset($constraint->fieldMap['id'])) {
            $transaction = $this->transactionRepository->find($value->{$constraint->fieldMap['id']});
            $data['position'] = $transaction->getPosition();
            $data['transactionDate'] = $transaction->getTransactionDate();
            $data['type'] = $transaction->getType();
        }
        if (isset($constraint->fieldMap['position']) && !is_null($value->{$constraint->fieldMap['position']})) {
            $data['position'] = $value->{$constraint->fieldMap['position']};
        }
        if (!is_null($value->{$constraint->fieldMap['transactionDate']})) {
            $data['transactionDate'] = $value->{$constraint->fieldMap['transactionDate']};
        }
        if (!is_null($value->{$constraint->fieldMap['type']})) {
            $data['type'] = $value->{$constraint->fieldMap['type']};
        }
        if (is_null($data['transactionDate']) || is_null($data['type'])) {
            return;
        }

        if (!$data['type']->isBalance()) {
            return;
        }
        $existing = $this->transactionRepository->getBalanceTransaction($data['position'], $data['transactionDate']);

        if (is_null($existing) || !is_null($transaction) && $transaction->getId() == $existing->getId()) {
            return;
        }
        $filed = !is_null($value->{$constraint->fieldMap['type']}) ? 'type' : 'transactionDate';
        $this->context->buildViolation($constraint->message)->atPath($filed)->addViolation();
    }
}
