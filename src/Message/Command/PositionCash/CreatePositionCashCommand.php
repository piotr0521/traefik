<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCash;

use Groshy\Message\Dto\PositionCash\CreatePositionCashDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionCashCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionCashDto $dto
    ) {
    }
}
