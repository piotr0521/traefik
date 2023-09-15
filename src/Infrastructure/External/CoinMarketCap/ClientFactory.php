<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\External\CoinMarketCap;

use coinmarketcap\Api as CoinMarketCapApi;

class ClientFactory
{
    public function __invoke(string $key): CoinMarketCapApi
    {
        return new CoinMarketCapApi($key);
    }
}
