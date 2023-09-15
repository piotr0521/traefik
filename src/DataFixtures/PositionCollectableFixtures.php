<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Message\Command\PositionCollectable\CreatePositionCollectableCommand;
use Groshy\Message\Dto\PositionCollectable\CreatePositionCollectableDto;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Talav\Component\User\Manager\UserManagerInterface;

final class PositionCollectableFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly UserManagerInterface $userManager,
        private readonly RepositoryInterface $assetCollectableRepository,
        private readonly RepositoryInterface $institutionRepository,
        private readonly RepositoryInterface $tagRepository,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function loadData(): void
    {
        return;
        $asset = $this->assetCollectableRepository->getCollectableAsset();
        $users = [
            'user2' => $this->faker->numberBetween(1, 5),
            'user4' => $this->faker->numberBetween(1, 5),
            'user8' => $this->faker->numberBetween(1, 5),
            'user7' => $this->faker->numberBetween(30, 40),
        ];

        foreach ($users as $userName => $number) {
            $user = $this->userManager->getRepository()->findOneBy(['username' => $userName]);
            $tags = $this->tagRepository->findBy(['createdBy' => $user]);
            for ($i = 0; $i <= $number; ++$i) {
                $dto = new CreatePositionCollectableDto();
                $dto->createdBy = $user;
                $dto->name = $this->faker->realTextBetween(10, 150);
                $dto->asset = $asset;
                $dto->tags = $this->faker->boolean() ? $this->faker->randomElements($tags, $this->faker->numberBetween(1, 3)) : [];
                $dto->notes = $this->faker->boolean() ? $this->faker->text(200) : null;

                $this->messageBus->dispatch(new CreatePositionCollectableCommand($dto))->last(HandledStamp::class)->getResult();
            }
        }
    }

    public function getOrder(): int
    {
        return 100;
    }
}
