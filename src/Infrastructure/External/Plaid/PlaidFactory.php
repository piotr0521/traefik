<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\External\Plaid;

use TomorrowIdeas\Plaid\Plaid;

class PlaidFactory
{
    public function __invoke(string $clientId, string $secret, string $env): Plaid
    {
        return new Plaid($clientId, $secret, $env);
    }
}
