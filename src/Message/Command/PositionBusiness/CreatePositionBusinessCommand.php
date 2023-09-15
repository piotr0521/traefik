<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionBusiness;

use Groshy\Message\Dto\PositionBusiness\CreatePositionBusinessDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionBusinessCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionBusinessDto $dto
    ) {
    }
}
