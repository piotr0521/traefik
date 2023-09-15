<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionInvestment;

use Groshy\Entity\PositionInvestment;
use Groshy\Message\Dto\PositionInvestment\UpdatePositionInvestmentDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionInvestmentCommand implements DomainEventInterface
{
    public function __construct(
        public PositionInvestment $resource,
        public UpdatePositionInvestmentDto $dto
    ) {
    }
}
