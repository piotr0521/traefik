<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionCrypto;

use Groshy\Message\Dto\PositionCrypto\CreatePositionCryptoDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreatePositionCryptoCommand implements DomainEventInterface
{
    public function __construct(
        public CreatePositionCryptoDto $dto
    ) {
    }
}
