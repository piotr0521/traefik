<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\State\Subscription;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Groshy\Message\Command\Subscription\CreateSubscriptionCommand;
use Groshy\Message\Dto\Subscription\CreateSubscriptionDto;
use Groshy\Presentation\Api\Dto\Subscription\ApiCreateSubscriptionDto;
use Groshy\Presentation\Api\Dto\Subscription\Subscription;
use Stripe\Subscription as StripeSubscription;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Webmozart\Assert\Assert;

class CreateSubscriptionProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly Security $security,
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        Assert::isInstanceOf($data, ApiCreateSubscriptionDto::class);
        $dto = new CreateSubscriptionDto($data->price, $this->security->getUser());
        /** @var StripeSubscription $subscription */
        $stripeSubscription = $this->bus->dispatch(new CreateSubscriptionCommand($dto))->last(HandledStamp::class)->getResult();
        $subscription = new Subscription();
        $subscription->id = $stripeSubscription->id;
        $subscription->clientSecret = $stripeSubscription->latest_invoice->payment_intent->client_secret;

        return $subscription;
    }
}
