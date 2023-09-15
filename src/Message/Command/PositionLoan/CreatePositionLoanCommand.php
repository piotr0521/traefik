<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionLoan;

use Groshy\Message\Dto\PositionLoan\CreatePositionLoanDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionLoanCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionLoanDto $dto
    ) {
    }
}
