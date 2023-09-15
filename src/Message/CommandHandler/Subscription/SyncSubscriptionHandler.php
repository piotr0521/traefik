<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Subscription;

use Groshy\Entity\Customer;
use Groshy\Entity\Price;
use Groshy\Entity\Subscription;
use Groshy\Entity\User;
use Groshy\Message\Command\Subscription\SyncSubscriptionCommand;
use Stripe\StripeClient;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class SyncSubscriptionHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly StripeClient $stripeClient,
        private readonly RepositoryInterface $priceRepository,
        private readonly RepositoryInterface $customerRepository,
        private readonly ManagerInterface $subscriptionManager,
        private readonly ManagerInterface $userManager,
    ) {
    }

    public function __invoke(SyncSubscriptionCommand $message): Subscription
    {
        $stripeSubscription = $this->stripeClient->subscriptions->retrieve($message->stripeSubscriptionId);
        Assert::notNull($stripeSubscription);

        $subscription = $this->getSubscription($stripeSubscription->id, $stripeSubscription->customer);
        $wasActive = $subscription->isActive();
        $subscription->setPrice($this->getPrice($stripeSubscription->items->first()->price->id));
        $subscription->setStatus($stripeSubscription->status);
        $this->subscriptionManager->update($subscription, true);

        $user = $subscription->getCustomer()->getUser();
        if ($wasActive && !$subscription->isActive()) {
            $user->removeRole(User::ROLE_CUSTOMER);
        } elseif (!$wasActive && $subscription->isActive()) {
            $user->addRole(User::ROLE_CUSTOMER);
        }
        $this->userManager->update($user, true);

        return $subscription;
    }

    private function getPrice(string $stripePriceId): Price
    {
        $price = $this->priceRepository->findOneBy(['stripeId' => $stripePriceId]);
        Assert::notNull($price);

        return $price;
    }

    private function getCustomer(string $stripeCustomerId): Customer
    {
        $customer = $this->customerRepository->findOneBy(['stripeId' => $stripeCustomerId]);
        Assert::notNull($customer);

        return $customer;
    }

    private function getSubscription(string $stripeSubscriptionId, string $stripeCustomerId): Subscription
    {
        $subscription = $this->subscriptionManager->getRepository()->findOneBy(['stripeId' => $stripeSubscriptionId]);
        if (is_null($subscription)) {
            /** @var Subscription $subscription */
            $subscription = $this->subscriptionManager->create();
            $subscription->setStripeId($stripeSubscriptionId);
            $subscription->setCustomer($this->getCustomer($stripeCustomerId));
        }

        return $subscription;
    }
}
