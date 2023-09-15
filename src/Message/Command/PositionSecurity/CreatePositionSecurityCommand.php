<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionSecurity;

use Groshy\Message\Dto\PositionSecurity\CreatePositionSecurityDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionSecurityCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionSecurityDto $dto
    ) {
    }
}
