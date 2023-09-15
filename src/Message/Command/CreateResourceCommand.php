<?php

declare(strict_types=1);

namespace Groshy\Message\Command;

use Talav\Component\Resource\Model\DomainEventInterface;

final class CreateResourceCommand implements DomainEventInterface
{
    public function __construct(
        public mixed $dto
    ) {
    }
}
