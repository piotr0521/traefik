<?php

declare(strict_types=1);

namespace Groshy\Message\Command\AccountHolder;

use Groshy\Message\Dto\AccountHolder\CreateAccountHolderDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreateAccountHolderCommand implements DomainEventInterface
{
    public function __construct(
        public CreateAccountHolderDto $dto
    ) {
    }
}
