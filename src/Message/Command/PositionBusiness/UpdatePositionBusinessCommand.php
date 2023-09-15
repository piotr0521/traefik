<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionBusiness;

use Groshy\Entity\PositionBusiness;
use Groshy\Message\Dto\PositionBusiness\UpdatePositionBusinessDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionBusinessCommand implements DomainEventInterface
{
    public function __construct(
        public PositionBusiness $resource,
        public UpdatePositionBusinessDto $dto
    ) {
    }
}
