<?php

declare(strict_types=1);

namespace Groshy\Message\Command\Subscription;

use Groshy\Message\Dto\Subscription\CreateSubscriptionDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreateSubscriptionCommand implements DomainEventInterface
{
    public function __construct(
        public CreateSubscriptionDto $dto
    ) {
    }
}
