<?php

declare(strict_types=1);

namespace Groshy\Message\Command\AccountHolder;

use Groshy\Entity\AccountHolder;
use Groshy\Message\Dto\AccountHolder\UpdateAccountHolderDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdateAccountHolderCommand implements DomainEventInterface
{
    public function __construct(
        public AccountHolder $resource,
        public UpdateAccountHolderDto $dto
    ) {
    }
}
