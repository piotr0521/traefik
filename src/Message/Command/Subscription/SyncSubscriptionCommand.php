<?php

declare(strict_types=1);

namespace Groshy\Message\Command\Subscription;

use Talav\Component\Resource\Model\DomainEventInterface;

final class SyncSubscriptionCommand implements DomainEventInterface
{
    public function __construct(
        public string $stripeSubscriptionId,
    ) {
    }
}
