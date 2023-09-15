<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCrypto;

use Groshy\Entity\PositionCrypto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeletePositionCryptoCommand implements DomainEventInterface
{
    public function __construct(
        public PositionCrypto $position
    ) {
    }
}
