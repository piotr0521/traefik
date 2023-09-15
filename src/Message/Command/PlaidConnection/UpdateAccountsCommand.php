<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PlaidConnection;

use Ramsey\Uuid\UuidInterface;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdateAccountsCommand implements DomainEventInterface
{
    public function __construct(
        public ?UuidInterface $userId,
        public ?string $itemId = null
    ) {
    }
}
