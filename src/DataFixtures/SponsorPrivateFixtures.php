<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\Sponsor;
use Groshy\Entity\User;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class SponsorPrivateFixtures extends BaseFixture implements OrderedFixtureInterface
{
    private array $users = ['user1', 'user2'];

    public function __construct(
        private readonly ManagerInterface $sponsorManager,
        private readonly RepositoryInterface $userRepository,
    ) {
    }

    public function loadData(): void
    {
        foreach ($this->userRepository->findBy(['username' => $this->users]) as $user) {
            for ($i = 1; $i <= $this->faker->numberBetween(5, 15); ++$i) {
                $this->createSponsor($user);
            }
        }
        $this->sponsorManager->flush();
    }

    public function getOrder(): int
    {
        return 100;
    }

    private function createSponsor(User $user): void
    {
        /** @var Sponsor $sponsor */
        $sponsor = $this->sponsorManager->create();
        $sponsor->setName($this->faker->company());
        $sponsor->setWebsite($this->faker->domainName());
        $sponsor->setPrivacy(Privacy::PRIVATE);
        $sponsor->setCreatedBy($user);
        $this->sponsorManager->update($sponsor);
    }
}
