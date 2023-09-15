<?php

declare(strict_types=1);

namespace Groshy\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Talav\UserBundle\Controller\Frontend\RegistrationController;
use Talav\UserBundle\Controller\Frontend\SecurityController;

class LoginRegistrationListener implements EventSubscriberInterface
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
        if (!$this->isRegistration($controller) && !$this->isLogin($controller)) {
            return;
        }
        if (!$this->security->isGranted('ROLE_USER')) {
            return;
        }
        $event->stopPropagation();
        $event->setController(static function () {
            return new RedirectResponse('/user/dashboard');
        });
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    private function isRegistration(array $controller): bool
    {
        if (!($controller[0] instanceof RegistrationController)) {
            return false;
        }

        return 'register' == $controller[1];
    }

    private function isLogin(array $controller): bool
    {
        if (!($controller[0] instanceof SecurityController)) {
            return false;
        }

        return 'login' == $controller[1];
    }
}
