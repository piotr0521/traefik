<?php

declare(strict_types=1);

namespace Groshy\Message\Command\AssetCryptoPrice;

use Talav\Component\Resource\Model\DomainEventInterface;

final class DownloadHistoryCommand implements DomainEventInterface
{
    public function __construct(
        public string $symbol
    ) {
    }
}
