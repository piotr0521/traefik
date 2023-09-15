<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Action\Token;

use Groshy\Message\Command\PlaidConnection\CreateConnectionCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class TokenExchangeAction
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly Security $security,
    ) {
    }

    public function __invoke(string $link): array
    {
        $this->bus->dispatch(new CreateConnectionCommand($link, $this->security->getUser()->getId()));

        return [];
    }
}
