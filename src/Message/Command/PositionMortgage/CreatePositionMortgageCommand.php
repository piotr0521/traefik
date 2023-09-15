<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionMortgage;

use Groshy\Message\Dto\PositionMortgage\CreatePositionMortgageDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionMortgageCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionMortgageDto $dto
    ) {
    }
}
