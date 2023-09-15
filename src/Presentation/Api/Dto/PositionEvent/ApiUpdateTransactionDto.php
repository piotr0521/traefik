<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionEvent;

use Groshy\Entity\Transaction;
use Groshy\Validator\Constraints\Money;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdateTransactionDto
{
    #[Assert\NotNull]
    public ?Transaction $transaction = null;

    #[Assert\Sequentially([
        new Money(),
        new Assert\GreaterThan(-10000000),
        new Assert\LessThan(value: 10000000, message: 'Amount is too large.'),
    ])]
    public mixed $amount = null;

    #[Assert\Type('float', message: 'This value is not a correct quantity')]
    public mixed $quantity = null;
}
