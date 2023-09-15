<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Message\Command\PositionBusiness\CreatePositionBusinessCommand;
use Groshy\Message\CommandHandler\PositionBusiness\CreatePositionBusinessHandler;
use Groshy\Message\Dto\PositionBusiness\CreatePositionBusinessDto;
use Groshy\Model\MoneyAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class PositionBusinessFixtures extends BaseFixture implements OrderedFixtureInterface
{
    use MoneyAwareTrait;

    public function __construct(
        private readonly RepositoryInterface $assetBusinessRepository,
        private readonly RepositoryInterface $userRepository,
        private readonly CreatePositionBusinessHandler $createHandler,
    ) {
    }

    public function loadData(): void
    {
        return;
        $users = [
            'user2' => $this->faker->numberBetween(1, 10),
            'user3' => $this->faker->numberBetween(1, 10),
            'user4' => $this->faker->numberBetween(1, 10),
            'user8' => $this->faker->numberBetween(30, 40),
        ];
        foreach ($users as $userName => $number) {
            $user = $this->userRepository->findOneBy(['username' => $userName]);
            for ($i = 0; $i <= $number; ++$i) {
                $dto = new CreatePositionBusinessDto();
                $dto->name = $this->faker->realTextBetween(10, 50);
                $dto->description = $this->faker->realTextBetween(10, 250);
                $dto->website = $this->faker->boolean ? $this->faker->url() : null;
                $dto->ownership = $this->faker->numberBetween(80, 100);
                $dto->createdBy = $user;
                $dto->notes = $this->faker->boolean ? $this->faker->text(200) : null;
                $dto->originalValue = $this->fromBase(strval($this->faker->numberBetween(400, 500) * 1000));
                $dto->originalDate = $this->faker->dateTimeBetween('-5 years', '-1 year');
                $dto->currentValue = $dto->originalValue->multiply(strval($this->faker->randomFloat(2, 1.2, 1.4)));
                $dto->valueDate = $this->faker->dateTimeBetween('-2 months', '-1 day');
                $this->createHandler->__invoke(new CreatePositionBusinessCommand($dto));
            }
        }
    }

    public function getOrder(): int
    {
        return 100;
    }
}
