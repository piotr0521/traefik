<?php

declare(strict_types=1);

namespace Groshy\Message\Command\AccountHolder;

use Groshy\Entity\AccountHolder;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeleteAccountHolderCommand implements DomainEventInterface
{
    public function __construct(
        public AccountHolder $resource
    ) {
    }
}
