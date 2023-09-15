<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionSecurity;

use Groshy\Entity\PositionSecurity;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionSecurityCommand implements DomainEventInterface
{
    public function __construct(
        public PositionSecurity $position
    ) {
    }
}
