<?php

declare(strict_types=1);

namespace Groshy\Tests\Helper;

use Groshy\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait UsersAwareTrait
{
    private array $users = [];

    public function setUpUsers(ContainerInterface $container): void
    {
        $userManager = $container->get('app.manager.user');
        foreach ($userManager->getRepository()->findAll() as $user) {
            $this->users[$user->getUsername()] = $user;
        }
    }

    public function getUser(string $username): User
    {
        return $this->users[$username];
    }

    /**
     * @param array<string> $usernames
     *
     * @return array<User>
     */
    public function getUsers(array $usernames): array
    {
        return array_map(fn ($username) => $this->getUser($username), $usernames);
    }
}
