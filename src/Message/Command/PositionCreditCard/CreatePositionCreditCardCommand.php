<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCreditCard;

use Groshy\Message\Dto\PositionCreditCard\CreatePositionCreditCardDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionCreditCardCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionCreditCardDto $dto
    ) {
    }
}
