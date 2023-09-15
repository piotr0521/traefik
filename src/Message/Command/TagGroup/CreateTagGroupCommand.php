<?php

declare(strict_types=1);

namespace Groshy\Message\Command\TagGroup;

use Groshy\Message\Dto\TagGroup\CreateTagGroupDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreateTagGroupCommand implements DomainEventInterface
{
    public function __construct(
        public CreateTagGroupDto $dto
    ) {
    }
}
