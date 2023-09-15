<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\External\AlphaVantage;

use AlphaVantage\Client;
use AlphaVantage\Options;

class AlphaVantageClientFactory
{
    public function __invoke(string $key): Client
    {
        $option = new Options();
        $option->setApiKey($key);

        return new Client($option);
    }
}
