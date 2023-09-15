<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Subscription;

use Groshy\Entity\User;
use Groshy\Infrastructure\External\Stripe\StripeManager;
use Groshy\Message\Command\Subscription\CreateSubscriptionCommand;
use Stripe\Subscription;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CreateSubscriptionHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly StripeManager $stripeManager,
    ) {
    }

    public function __invoke(CreateSubscriptionCommand $message): Subscription
    {
        /** @var User $user */
        $user = $message->dto->createdBy;
        $price = $message->dto->price;
        if (is_null($user->getCustomer())) {
            $this->stripeManager->createCustomer($user);
        }

        return $this->stripeManager->createStripeSubscription($user, $price);
    }
}
