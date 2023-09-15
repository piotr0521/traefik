<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionInvestment;

use Groshy\Message\Dto\PositionInvestment\CreatePositionInvestmentDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionInvestmentCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionInvestmentDto $dto
    ) {
    }
}
