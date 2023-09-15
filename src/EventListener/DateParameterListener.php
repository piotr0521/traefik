<?php

declare(strict_types=1);

namespace Groshy\EventListener;

use DateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/** Defines and validates to and from parameters for every request */
class DateParameterListener implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event)
    {
        $to = $this->getQueryDate($event->getRequest(), 'to', new DateTime());
        $from = $this->getQueryDate($event->getRequest(), 'from', new DateTime('-1 month'));
        if ($from > $to) {
            $from = clone $to;
            $from->modify('-1 month');
        }

        $event->getRequest()->query->set('to', $to->format('Y-m-d'));
        $event->getRequest()->query->set('from', $from->format('Y-m-d'));
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 15]],
        ];
    }

    private function getQueryDate(Request $request, string $key, DateTime $default): DateTime
    {
        $value = $request->query->get($key);
        if (is_null($value)) {
            return $default;
        }
        $result = DateTime::createFromFormat('Y-m-d', $value);
        if (!$result) {
            return $default;
        }

        return $result;
    }
}
