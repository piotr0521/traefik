<?php

declare(strict_types=1);

namespace Groshy\Message\Command\TagGroup;

use Groshy\Entity\TagGroup;
use Groshy\Message\Dto\TagGroup\UpdateTagGroupDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdateTagGroupCommand implements DomainEventInterface
{
    public function __construct(
        public TagGroup $tagGroup,
        public UpdateTagGroupDto $dto
    ) {
    }
}
