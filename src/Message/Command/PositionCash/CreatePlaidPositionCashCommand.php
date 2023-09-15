<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCash;

use Groshy\Message\Dto\PositionCash\CreatePlaidPositionCashDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePlaidPositionCashCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePlaidPositionCashDto $dto
    ) {
    }
}
