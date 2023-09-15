<?php

declare(strict_types=1);

namespace Groshy\Message\Command\TagGroup;

use Groshy\Entity\TagGroup;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeleteTagGroupCommand implements DomainEventInterface
{
    public function __construct(
        public TagGroup $tagGroup
    ) {
    }
}
