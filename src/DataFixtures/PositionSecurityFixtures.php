<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Message\Command\PositionSecurity\CreatePositionSecurityCommand;
use Groshy\Message\Dto\PositionSecurity\CreatePositionSecurityDto;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class PositionSecurityFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly RepositoryInterface $userRepository,
        private readonly RepositoryInterface $assetSecurityRepository,
        private readonly RepositoryInterface $assetSecurityPriceRepository,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function loadData(): void
    {
        return;
        $users = [
            'user1' => $this->faker->numberBetween(1, 3),
            'user2' => $this->faker->numberBetween(1, 3),
            'user4' => $this->faker->numberBetween(1, 2),
            'user5' => $this->faker->numberBetween(1, 2),
            'user8' => $this->faker->numberBetween(1, 3),
            'user7' => 5,
            'user15' => 1,
        ];

        $assets = $this->assetSecurityRepository->findBy(['symbol' => ['AAPL', 'ABNB', 'BKNG', 'GOOGL', 'T', 'AMZN']]);
        foreach ($users as $username => $count) {
            $user = $this->userRepository->findOneBy(['username' => $username]);
            $userAssets = $this->faker->randomElements($assets, $count);
            foreach ($userAssets as $asset) {
                $price = $this->faker->randomElement($this->assetSecurityPriceRepository->findBy(['asset' => $asset]));
                $dto = new CreatePositionSecurityDto();
                $dto->asset = $asset;
                $dto->createdBy = $user;
                $dto->notes = $this->faker->text(200);
                $dto->quantity = $this->faker->numberBetween(10, 100);
                $dto->purchaseDate = $price->getPricedAt();
                $dto->averagePrice = $price->getPrice();
                $this->messageBus->dispatch(new CreatePositionSecurityCommand($dto));
            }
        }
    }

    public function getOrder(): int
    {
        return 100;
    }
}
