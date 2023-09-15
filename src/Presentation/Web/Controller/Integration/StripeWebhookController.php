<?php

declare(strict_types=1);

namespace Groshy\Presentation\Web\Controller\Integration;

use Exception;
use Groshy\Message\Command\Subscription\SyncSubscriptionCommand;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Stripe\Event;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Talav\Component\Resource\Model\DomainEventInterface;

#[Route('/integration/stripe')]
class StripeWebhookController extends AbstractController
{
    public function __construct(
        private readonly StripeClient $stripeClient,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/webhooks', name: 'groshy_integration_stripewebhook_webhooks')]
    public function webhooksAction(Request $request): Response
    {
        $requestData = json_decode($request->getContent());
        if (!isset($requestData->id) || !isset($requestData->object)) {
            throw new RuntimeException('Invalid webhook request data');
        }
        if ('event' !== $requestData->object) {
            throw new RuntimeException('Unknown stripe object type in webhook');
        }
        try {
            $stripeEvent = $this->stripeClient->events->retrieve($requestData->id);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

        if (!$stripeEvent) {
            throw new RuntimeException(sprintf('Event does not exists, id %s', $requestData->id));
        }
        $this->logger->debug('New event from stripe: '.$stripeEvent);
        $command = $this->defineCommand($stripeEvent);
        if (!is_null($command)) {
            $this->bus->dispatch($command);
        }

        return new Response();
    }

    private function defineCommand(Event $event): ?DomainEventInterface
    {
        switch ($event->type) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.paused':
            case 'customer.subscription.resumed':
            case 'customer.subscription.deleted':
                return new SyncSubscriptionCommand($event->data->object->id);
            case 'customer.subscription.trial_will_end': return null;
            case 'invoice.upcoming': return null;
        }

        return null;
    }
}
