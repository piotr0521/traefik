<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\PositionCash;
use Groshy\Message\Command\PositionCash\CreatePositionCashCommand;
use Groshy\Message\Command\PositionEvent\CreatePositionEventListCommand;
use Groshy\Message\Dto\PositionCash\CreatePositionCashDto;
use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\PositionValueDto;
use Money\Currency;
use Money\Money;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class PositionCashFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly ManagerInterface $positionCashManager,
        private readonly RepositoryInterface $userRepository,
        private readonly RepositoryInterface $institutionRepository,
        private readonly RepositoryInterface $accountTypeRepository,
        private readonly RepositoryInterface $accountHolderRepository,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function loadData(): void
    {
        $users = [
            'user1' => $this->faker->numberBetween(1, 3),
            'user2' => $this->faker->numberBetween(1, 3),
            'user3' => $this->faker->numberBetween(1, 3),
            'user4' => $this->faker->numberBetween(1, 3),
            'user5' => $this->faker->numberBetween(1, 3),
            'user6' => $this->faker->numberBetween(1, 3),
            'user7' => $this->faker->numberBetween(1, 3),
            'user8' => $this->faker->numberBetween(1, 3),
            'user10' => 1,
            'user11' => $this->faker->numberBetween(10, 20),
        ];
        $institutions = $this->institutionRepository->findAll();
        $list = [];
        foreach ($users as $userName => $count) {
            $user = $this->userRepository->findOneBy(['username' => $userName]);
            for ($i = 0; $i < $count; ++$i) {
                $dto = new CreatePositionCashDto();
                $dto->notes = $this->faker->boolean ? $this->faker->text(100) : null;
                $dto->createdBy = $user;
                $dto->name = $this->faker->company().' Cash Account';
                $dto->institution = $this->faker->randomElement($institutions);
                $dto->accountType = $this->accountTypeRepository->findOneBy(['name' => 'Checking']);
                $dto->accountHolder = $this->accountHolderRepository->findOneBy(['createdBy' => $user]);
                $dto->yield = $this->faker->randomFloat(4, 0.001, 0.003);
                $pos = $this->messageBus->dispatch(new CreatePositionCashCommand($dto))->last(HandledStamp::class)->getResult();

                $dto = new CreatePositionEventDto();
                $dto->value = new PositionValueDto();
                $dto->position = $pos;
                $dto->date = $this->faker->dateTimeBetween('-5 years', '-2 months');
                $dto->value->amount = new Money($this->faker->numberBetween(100000, 1000000), new Currency('USD'));
                $dto->type = PositionEventType::VALUE_UPDATE;
                $list[] = $dto;
            }
        }
        $this->positionCashManager->flush();
        $this->messageBus->dispatch(new CreatePositionEventListCommand($list));
        $list = [];

        /** @var array<PositionCash> $positions */
        $positions = $this->positionCashManager->getRepository()->findAll();
        foreach ($positions as $position) {
            $amount = $position->getLastValue()->getAmount();
            $period = new DatePeriod(
                $position->getStartDate(),
                new DateInterval('P1M'),
                new DateTime(),
                DatePeriod::EXCLUDE_START_DATE
            );
            foreach ($period as $date) {
                $dto = new CreatePositionEventDto();
                $dto->position = $position;
                $dto->date = $date;
                $dto->value = PositionValueDto::factory(amount: $amount->multiply(strval($this->faker->randomFloat(2, '0.98', '1.05'))));
                $dto->type = PositionEventType::VALUE_UPDATE;
                $list[] = $dto;
            }
        }
        $this->positionCashManager->flush();
        $this->messageBus->dispatch(new CreatePositionEventListCommand($list));
    }

    public function getOrder(): int
    {
        return 31;
    }
}
