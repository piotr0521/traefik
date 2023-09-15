<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Message\Command\PlaidConnection\CreateConnectionCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;
use TomorrowIdeas\Plaid\Plaid;

final class PlaidConnectionFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly ManagerInterface $plaidConnectionManager,
        private readonly RepositoryInterface $userRepository,
        private readonly RepositoryInterface $institutionRepository,
        private readonly Plaid $plaid,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function loadData(): void
    {
        return;
        $institutions = $this->institutionRepository->getPlaidInstitutions();
        $users = [
            'user2' => $this->faker->numberBetween(1, 2),
//            'user4' => $this->faker->numberBetween(1, 2),
//            'user9' => $this->faker->numberBetween(1, 2),
//            'user6' => 5,
        ];

        foreach ($users as $userName => $number) {
            $user = $this->userRepository->findOneBy(['username' => $userName]);
            $ins = $this->faker->randomElements($institutions, $number);
            foreach ($ins as $institution) {
                $token = $this->plaid->sandbox->createPublicToken($institution->getPlaidId(), ['transactions'])->public_token;
                $this->messageBus->dispatch(new CreateConnectionCommand($token, $user->getId()));
            }
//            sleep(60);
            $this->plaidConnectionManager->flush();
        }
    }

    public function getOrder(): int
    {
        return 25;
    }
}
