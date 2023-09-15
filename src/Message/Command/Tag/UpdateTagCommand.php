<?php

declare(strict_types=1);

namespace Groshy\Message\Command\Tag;

use Groshy\Entity\Tag;
use Groshy\Message\Dto\Tag\UpdateTagDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdateTagCommand implements DomainEventInterface
{
    public function __construct(
        public Tag $tag,
        public UpdateTagDto $dto
    ) {
    }
}
