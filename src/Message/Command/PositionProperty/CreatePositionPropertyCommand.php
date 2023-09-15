<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionProperty;

use Groshy\Message\Dto\PositionProperty\CreatePositionPropertyDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionPropertyCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionPropertyDto $dto
    ) {
    }
}
