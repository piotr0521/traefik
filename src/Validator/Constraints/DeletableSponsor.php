<?php

declare(strict_types=1);

namespace Groshy\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class DeletableSponsor extends Constraint
{
    public string $message = 'Sponsor cannot be deleted';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
