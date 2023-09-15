<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionMortgage;

use Groshy\Entity\PositionMortgage;
use Groshy\Message\Dto\PositionMortgage\UpdatePositionMortgageDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionMortgageCommand implements DomainEventInterface
{
    public function __construct(
        public PositionMortgage $resource,
        public UpdatePositionMortgageDto $dto
    ) {
    }
}
