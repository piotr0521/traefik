<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Message\Command\PositionMortgage\CreatePositionMortgageCommand;
use Groshy\Message\Dto\PositionMortgage\CreatePositionMortgageDto;
use Groshy\Model\MoneyAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Talav\Component\User\Manager\UserManagerInterface;

final class PositionMortgageFixtures extends BaseFixture implements OrderedFixtureInterface
{
    use MoneyAwareTrait;

    public function __construct(
        private readonly UserManagerInterface $userManager,
        private readonly RepositoryInterface $institutionRepository,
        private readonly RepositoryInterface $tagRepository,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function loadData(): void
    {
        return;
        $institutions = $this->institutionRepository->findAll();
        $users = [
            'user2' => $this->faker->numberBetween(1, 5),
            'user4' => $this->faker->numberBetween(1, 5),
            'user9' => $this->faker->numberBetween(1, 5),
            'user6' => $this->faker->numberBetween(30, 40),
        ];

        foreach ($users as $userName => $number) {
            $user = $this->userManager->getRepository()->findOneBy(['username' => $userName]);
            $tags = $this->tagRepository->findBy(['createdBy' => $user]);
            for ($i = 0; $i <= $number; ++$i) {
                $dto = new CreatePositionMortgageDto();
                $dto->createdBy = $user;
                $dto->name = $this->faker->realTextBetween(10, 150);
                $dto->interest = $this->faker->randomFloat(2, 2.25, 7.5);
                $dto->mortgageAmount = $this->fromBase(strval($this->faker->numberBetween(100, 300) * 1000));
                $dto->tags = $this->faker->boolean() ? $this->faker->randomElements($tags, $this->faker->numberBetween(1, 3)) : [];
                $dto->notes = $this->faker->boolean() ? $this->faker->text(200) : null;
                $dto->institution = $this->faker->boolean() ? $this->faker->randomElement($institutions) : null;
                $dto->mortgageDate = $this->faker->dateTimeBetween('-3 years', '-2 year');
                $dto->terms = $this->faker->numberBetween(15, 40);

                $position = $this->messageBus->dispatch(new CreatePositionMortgageCommand($dto))->last(HandledStamp::class)->getResult();
            }
        }
    }

    public function getOrder(): int
    {
        return 100;
    }
}
