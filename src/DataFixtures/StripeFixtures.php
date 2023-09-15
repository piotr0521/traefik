<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Entity\Customer;
use Groshy\Entity\Price;
use Groshy\Entity\Product;
use Groshy\Entity\User;
use Money\Money;
use Stripe\Customer as StripeCustomer;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\StripeClient;
use Talav\Component\Resource\Manager\ManagerInterface;
use Webmozart\Assert\Assert;

final class StripeFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly ManagerInterface $productManager,
        private readonly ManagerInterface $priceManager,
        private readonly ManagerInterface $customerManager,
        private readonly ManagerInterface $userManager,
        private readonly StripeClient $client
    ) {
    }

    public function loadData(): void
    {
        $stripeProduct = null;
        $data = $this->client->products->all();
        foreach ($data as $stripeProductCandidate) {
            if ('Groshy.io Premium Plan' == $stripeProductCandidate->name) {
                $stripeProduct = $stripeProductCandidate;
            }
        }
        Assert::notNull($stripeProduct);
        /** @var Product $product */
        $product = $this->productManager->create();
        $product->setName($stripeProduct->name);
        $product->setStripeId($stripeProduct->id);
        $this->productManager->update($product);

        $prices = $this->client->prices->all(['product' => $stripeProduct->id]);
        foreach ($prices as $stripePrice) {
            /** @var Price $price */
            $price = $this->priceManager->create();
            $price->setIsActive($stripePrice->active);
            $price->setAmount(Money::USD($stripePrice->unit_amount));
            $price->setStripeId($stripePrice->id);
            $price->setRecurringInterval($stripePrice->recurring->interval);
            $price->setRecurringIntervalCount($stripePrice->recurring->interval_count);
            $product->addPrice($price);
            $this->priceManager->update($price);
        }
        $this->loadCustomers();
        $this->productManager->flush();
    }

    public function getOrder(): int
    {
        return 11;
    }

    private function loadCustomers()
    {
        foreach ($this->userManager->getRepository()->findAll() as $user) {
            $this->ensureCustomer($user, $this->getIndexedStripeCustomers());
        }
    }

    private function getIndexedStripeCustomers(): array
    {
        $stripeCustomers = $this->client->customers->all(['limit' => 100]);
        $stripeCustomersMap = [];
        foreach ($stripeCustomers as $stripeCustomer) {
            $stripeCustomersMap[$stripeCustomer->email] = $stripeCustomer;
        }

        return $stripeCustomersMap;
    }

    private function ensureCustomer(User $user, array $stripeCustomers): void
    {
        if (isset($stripeCustomers[$user->getEmail()])) {
            $stripeCustomer = $stripeCustomers[$user->getEmail()];
        } else {
            $stripeCustomer = $this->createStripeCustomer($user);
        }
        /** @var Customer $customer */
        $customer = $this->customerManager->create();
        $customer->setUser($user);
        $customer->setIsDelinquent($stripeCustomer->delinquent);
        $customer->setStripeId($stripeCustomer->id);
        $this->customerManager->update($customer);
    }

    private function createStripeCustomer(User $user): StripeCustomer
    {
        return $this->client->customers->create([
            'email' => $user->getEmail(),
            'name' => $user->getFullName(),
        ]);
    }

    private function ensurePaymentMethod(User $user)
    {
        $methods = $this->client->customers->allPaymentMethods($user->getCustomer()->getStripeId())['data'];
        if (0 == count($methods)) {
            $method = $this->createPaymentMethod();
            $this->client->paymentMethods->attach(
                $method->id,
                ['customer' => $user->getCustomer()->getStripeId()]
            );
        }
    }

    private function createPaymentMethod(): StripePaymentMethod
    {
        return $this->client->paymentMethods->create([
            'type' => 'card',
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 8,
                'exp_year' => 2029,
                'cvc' => '314',
            ],
        ]);
    }
}
