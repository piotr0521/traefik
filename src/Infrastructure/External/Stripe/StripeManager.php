<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\External\Stripe;

use Groshy\Entity\Customer;
use Groshy\Entity\Price;
use Groshy\Entity\User;
use Stripe\StripeClient;
use Stripe\Subscription;
use Talav\Component\Resource\Manager\ManagerInterface;

class StripeManager
{
    public function __construct(
        private readonly StripeClient $client,
        private readonly ManagerInterface $customerManager,
    ) {
    }

    public function createCustomer(User $user): Customer
    {
        $stripeCustomer = $this->client->customers->create([
            'email' => $user->getEmail(),
            'name' => $user->getFullName(),
        ]);
        /** @var Customer $customer */
        $customer = $this->customerManager->create();
        $customer->setUser($user);
        $customer->setIsDelinquent($stripeCustomer->delinquent);
        $customer->setStripeId($stripeCustomer->id);
        $this->customerManager->update($customer);

        return $customer;
    }

    public function createStripeSubscription(User $user, Price $price): Subscription
    {
        return $this->client->subscriptions->create([
            'customer' => $user->getCustomer()->getStripeId(),
            'items' => [[
                'price' => $price->getStripeId(),
            ]],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.payment_intent'],
        ]);
    }
}
