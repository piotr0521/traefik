<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\AssetInvestment;
use Groshy\Entity\Sponsor;
use Groshy\Entity\User;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class AssetInvestmentPrivateFixtures extends BaseFixture implements OrderedFixtureInterface
{
    private array $users = ['user1', 'user2'];

    private array $typeNames = ['Real Estate GP Fund', 'Real Estate LP Fund', 'Private Equity LP Fund'];

    public function __construct(
        private readonly RepositoryInterface $sponsorRepository,
        private readonly RepositoryInterface $userRepository,
        private readonly RepositoryInterface $assetTypeRepository,
        private readonly ManagerInterface $assetInvestmentManager,
    ) {
    }

    public function loadData(): void
    {
        $types = $this->assetTypeRepository->findBy(['name' => $this->typeNames]);
        foreach ($this->userRepository->findBy(['username' => $this->users]) as $user) {
            $sponsors = $this->sponsorRepository->findBy(['createdBy' => $user]);
            for ($i = 1; $i <= $this->faker->numberBetween(10, 20); ++$i) {
                $this->createInvestment($user, $sponsors, $types);
            }
        }
        $this->assetInvestmentManager->flush();
    }

    public function getOrder(): int
    {
        return 200;
    }

    private function createInvestment(User $user, array $sponsors, array $types): void
    {
        /** @var Sponsor $sponsor */
        $sponsor = $this->faker->randomElement($sponsors);
        /** @var AssetInvestment $asset */
        $asset = $this->assetInvestmentManager->create();
        $asset->setName($sponsor->getName().' Investment Fund '.$this->faker->numberBetween(1, 9));
        $asset->setPrivacy(Privacy::PRIVATE);
        $this->faker->boolean ? $asset->setSponsor($sponsor) : null;
        $asset->setCreatedBy($user);
        $asset->setAssetType($this->faker->randomElement($types));
        $this->assetInvestmentManager->update($asset);
    }
}
