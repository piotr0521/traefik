<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Domain\Enum\PropertyType;
use Groshy\Message\Command\PositionProperty\CreatePositionPropertyCommand;
use Groshy\Message\CommandHandler\PositionProperty\CreatePositionPropertyHandler;
use Groshy\Message\Dto\PositionProperty\CreatePositionPropertyDto;
use Groshy\Model\MoneyAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class PositionPropertyFixtures extends BaseFixture implements OrderedFixtureInterface
{
    use MoneyAwareTrait;

    public function __construct(
        private readonly RepositoryInterface $assetPropertyRepository,
        private readonly RepositoryInterface $userRepository,
        private readonly CreatePositionPropertyHandler $createHandler,
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
                $dto = new CreatePositionPropertyDto();
                $dto->name = $this->faker->realTextBetween(10, 50);
                $dto->propertyType = $this->faker->randomElement(PropertyType::cases());
                $dto->website = $this->faker->boolean ? $this->faker->url() : null;
                $dto->address = $this->faker->boolean ? str_replace("\n", ' ', $this->faker->address()) : null;
                $dto->units = $this->faker->boolean ? $this->faker->numberBetween(2, 450) : null;
                $dto->createdBy = $user;
                $dto->notes = $this->faker->boolean ? $this->faker->text(200) : null;
                $dto->purchaseValue = $this->fromBase(strval($this->faker->numberBetween(400, 500) * 1000));
                $dto->currentValue = $dto->purchaseValue->multiply(strval($this->faker->randomFloat(2, 1.2, 1.4)));
                $dto->purchaseDate = $this->faker->dateTimeBetween('-5 years', '-1 year');
                $this->createHandler->__invoke(new CreatePositionPropertyCommand($dto));
            }
        }
    }

    public function getOrder(): int
    {
        return 100;
    }
}
