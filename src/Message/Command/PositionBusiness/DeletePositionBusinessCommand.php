<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionBusiness;

use Groshy\Entity\PositionBusiness;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionBusinessCommand implements DomainEventInterface
{
    public function __construct(
        public PositionBusiness $position
    ) {
    }
}
