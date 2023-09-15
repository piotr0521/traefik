<?php

declare(strict_types=1);

namespace Groshy\EventListener;

use Groshy\Presentation\Web\Controller\CustomerRequiredInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CustomerListener implements EventSubscriberInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }
        if (!($controller[0] instanceof CustomerRequiredInterface)) {
            return;
        }
        if ($this->security->isGranted('ROLE_CUSTOMER')) {
            return;
        }
        $event->stopPropagation();
        $event->setController(static function () {
            return new RedirectResponse('/checkout');
        });
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
