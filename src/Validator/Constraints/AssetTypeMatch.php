<?php

declare(strict_types=1);

namespace Groshy\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class AssetTypeMatch extends Constraint
{
    public string $message = 'This asset type is not compatible with provided asset or position';

    public function __construct(
        public ?string $assetClass = null,
        array $groups = null,
        mixed $payload = null,
        array $options = []
    ) {
        parent::__construct($options, $groups, $payload);
    }
}
