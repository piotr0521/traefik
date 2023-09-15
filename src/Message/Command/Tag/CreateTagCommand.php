<?php

declare(strict_types=1);

namespace Groshy\Message\Command\Tag;

use Groshy\Message\Dto\Tag\CreateTagDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreateTagCommand implements DomainEventInterface
{
    public function __construct(
        public CreateTagDto $dto
    ) {
    }
}
