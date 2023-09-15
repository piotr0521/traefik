<?php

declare(strict_types=1);

namespace Groshy\Message\Command\Tag;

use Groshy\Entity\Tag;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeleteTagCommand implements DomainEventInterface
{
    public function __construct(
        public Tag $tag
    ) {
    }
}
