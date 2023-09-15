<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCrypto;

use Groshy\Entity\PositionCrypto;
use Groshy\Message\Dto\PositionCrypto\UpdatePositionCryptoDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdatePositionCryptoCommand implements DomainEventInterface
{
    public function __construct(
        public PositionCrypto $resource,
        public UpdatePositionCryptoDto $dto
    ) {
    }
}
