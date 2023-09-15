<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\Position;
use Groshy\Entity\User;
use Groshy\Message\Command\PositionCreditCard\CreatePositionCreditCardCommand;
use Groshy\Message\Command\PositionEvent\CreatePositionEventListCommand;
use Groshy\Message\Dto\PositionCreditCard\CreatePositionCreditCardDto;
use Money\Money;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Talav\Component\User\Manager\UserManagerInterface;

final class PositionLiabilitiesFixtures extends PositionBaseFixtures implements OrderedFixtureInterface
{
    public function __construct(
        protected readonly UserManagerInterface $userManager,
        protected readonly RepositoryInterface $assetInvestmentRepository,
        protected readonly RepositoryInterface $positionInvestmentRepository,
        protected readonly RepositoryInterface $assetTypeRepository,
        protected readonly RepositoryInterface $tagRepository,
        private readonly RepositoryInterface $accountHolderRepository,
        protected readonly MessageBusInterface $messageBus
    ) {
    }

    public function loadData(): void
    {
        $users = [
            'user1' => $this->faker->numberBetween(1, 2),
            'user2' => $this->faker->numberBetween(1, 2),
            'user3' => $this->faker->numberBetween(1, 2),
            'user4' => $this->faker->numberBetween(1, 2),
            'user5' => $this->faker->numberBetween(1, 2),
            'user19' => $this->faker->numberBetween(10, 20),
        ];
        $list = [];
        $positions = [];
        foreach ($users as $userName => $number) {
            $user = $this->userManager->getRepository()->findOneBy(['username' => $userName]);
            for ($i = 0; $i <= $number; ++$i) {
                $cardLimit = Money::USD($this->faker->numberBetween(10, 100) * 1000 * 100);
                $positions[] = $position = $this->createPositionCreditCard($user, $cardLimit);
                $list[] = $this->buildValueUpdateEvent(
                    position: $position,
                    date: $this->faker->dateTimeBetween('-5 years', '-2 months'),
                    amount: $cardLimit->multiply(strval($this->faker->randomFloat(2, '0.01', '0.1'))),
                    type: PositionEventType::BALANCE_UPDATE
                );
            }
        }

        $this->messageBus->dispatch(new CreatePositionEventListCommand($list));
        $list = [];

        foreach ($positions as $position) {
            $amount = $position->getLastValue()->getAmount();
            $period = new DatePeriod(
                $position->getStartDate(),
                new DateInterval('P1M'),
                new DateTime(),
                DatePeriod::EXCLUDE_START_DATE
            );
            foreach ($period as $date) {
                $list[] = $this->buildValueUpdateEvent(
                    position: $position,
                    date: $date,
                    amount: $amount->multiply(strval($this->faker->randomFloat(2, '0.95', '1.05'))),
                    type: PositionEventType::BALANCE_UPDATE
                );
            }
        }
        $this->messageBus->dispatch(new CreatePositionEventListCommand($list));
    }

    public function getOrder(): int
    {
        return 30;
    }

    protected function createPositionCreditCard(User $user, Money $cardLimit): Position
    {
        $dto = new CreatePositionCreditCardDto();
        $dto->createdBy = $user;
        $dto->name = $this->faker->company().' Credit Card';
        $dto->notes = $this->fakeNotes();
        $dto->tags = $this->fakeTags($user);
        $dto->cardLimit = $cardLimit;
        $dto->accountHolder = $this->accountHolderRepository->findOneBy(['createdBy' => $user]);

        return $this->messageBus->dispatch(new CreatePositionCreditCardCommand($dto))->last(HandledStamp::class)->getResult();
    }
}
