<?php

declare(strict_types=1);

namespace Groshy\Message\Command\Tag;

use Talav\Component\Resource\Model\DomainEventInterface;
use Talav\Component\User\Model\UserInterface;

final class ResetTagsCommand implements DomainEventInterface
{
    public function __construct(
        public UserInterface $user
    ) {
    }
}
