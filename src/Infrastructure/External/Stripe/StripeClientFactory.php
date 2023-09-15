<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\External\Stripe;

use Stripe\StripeClient;

class StripeClientFactory
{
    public function __invoke(string $key): StripeClient
    {
        return new StripeClient($key);
    }
}
