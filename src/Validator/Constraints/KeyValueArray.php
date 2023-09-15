<?php

declare(strict_types=1);

namespace Groshy\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class KeyValueArray extends Constraint
{
    public $message = 'Data should be in a key-value format';
}
