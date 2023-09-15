<?php

declare(strict_types=1);

namespace Groshy\Message\Command;

use Talav\Component\Resource\Model\DomainEventInterface;
use Talav\Component\Resource\Model\ResourceInterface;

final class UpdateResourceCommand implements DomainEventInterface
{
    public function __construct(
        public ResourceInterface $resource,
        public mixed $dto
    ) {
    }
}
