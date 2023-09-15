<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PlaidConnection;

use Ramsey\Uuid\UuidInterface;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreateConnectionCommand implements DomainEventInterface
{
    public function __construct(
        public ?string $publicToken,
        public ?UuidInterface $userId
    ) {
    }
}
