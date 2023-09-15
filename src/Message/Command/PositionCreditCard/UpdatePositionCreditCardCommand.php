<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCreditCard;

use Groshy\Entity\PositionCreditCard;
use Groshy\Message\Dto\PositionCreditCard\UpdatePositionCreditCardDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionCreditCardCommand implements DomainEventInterface
{
    public function __construct(
        public PositionCreditCard $resource,
        public UpdatePositionCreditCardDto $dto
    ) {
    }
}
