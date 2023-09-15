<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionSecurity;

use Groshy\Entity\PositionSecurity;
use Groshy\Message\Dto\PositionSecurity\UpdatePositionSecurityDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionSecurityCommand implements DomainEventInterface
{
    public function __construct(
        public PositionSecurity $resource,
        public UpdatePositionSecurityDto $dto
    ) {
    }
}
