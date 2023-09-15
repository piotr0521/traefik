<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCreditCard;

use Groshy\Entity\PositionCreditCard;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionCreditCardCommand implements DomainEventInterface
{
    public function __construct(
        public PositionCreditCard $position
    ) {
    }
}
