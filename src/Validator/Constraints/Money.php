<?php

declare(strict_types=1);

namespace Groshy\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class Money extends Constraint
{
    public string $message = 'This value is not a correct amount.';
}
