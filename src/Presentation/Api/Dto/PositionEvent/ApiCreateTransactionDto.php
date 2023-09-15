<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionEvent;

use Groshy\Validator\Constraints\Money;
use Symfony\Component\Validator\Constraints as Assert;

class ApiCreateTransactionDto
{
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Sequentially([
            new Money(),
            new Assert\GreaterThan(-10000000),
            new Assert\LessThan(value: 10000000, message: 'Amount is too large.'),
        ]),
    ])]
    public mixed $amount = null;

    #[Assert\Type('float', message: 'This value is not a correct quantity')]
    public mixed $quantity = null;
}
