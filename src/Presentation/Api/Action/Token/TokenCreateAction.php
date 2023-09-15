<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Action\Token;

use Groshy\Presentation\Api\Dto\Token\Token;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use TomorrowIdeas\Plaid\Entities\User;
use TomorrowIdeas\Plaid\Plaid;

class TokenCreateAction
{
    public function __construct(
        private readonly Plaid $plaid,
        private readonly Security $security,
    ) {
    }

    public function __invoke(Request $request): Token
    {
        $user = $this->security->getUser();
        $plaidUser = new User(
            id: $user->getId()->__toString(),
            name: $user->getFullName(),
            email_address: $user->getEmail()
        );
        $token = new Token();
        $response = $this->plaid->tokens->create('Groshy.io', 'en', ['US', 'CA'], $plaidUser, ['transactions']);
        $token->link = $response->link_token;

        return $token;
    }
}
