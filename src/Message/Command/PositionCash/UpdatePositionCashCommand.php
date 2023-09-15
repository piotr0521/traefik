<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCash;

use Groshy\Entity\PositionCash;
use Groshy\Message\Dto\PositionCash\UpdatePositionCashDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionCashCommand implements DomainEventInterface
{
    public function __construct(
        public PositionCash $resource,
        public UpdatePositionCashDto $dto
    ) {
    }
}
