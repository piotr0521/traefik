<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use DateTime;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\AssetInvestment;
use Groshy\Entity\Position;
use Groshy\Entity\PositionInvestment;
use Groshy\Entity\Tag;
use Groshy\Entity\User;
use Groshy\Message\Command\PositionEvent\CreatePositionEventListCommand;
use Groshy\Message\Command\PositionInvestment\CreatePositionInvestmentCommand;
use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\CreateTransactionDto;
use Groshy\Message\Dto\PositionEvent\PositionValueDto;
use Groshy\Message\Dto\PositionInvestment\CreatePositionInvestmentDto;
use Money\Money;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Talav\Component\User\Manager\UserManagerInterface;

abstract class PositionBaseFixtures extends BaseFixture implements OrderedFixtureInterface
{
    protected array $tags = [];

    public function __construct(
        protected readonly UserManagerInterface $userManager,
        protected readonly RepositoryInterface $assetInvestmentRepository,
        protected readonly RepositoryInterface $positionInvestmentRepository,
        protected readonly RepositoryInterface $assetTypeRepository,
        protected readonly RepositoryInterface $tagRepository,
        protected readonly MessageBusInterface $messageBus
    ) {
    }

    protected function createPositionInvestmentAndEvents(array $users, array $assets)
    {
        $list = [];
        foreach ($users as $userName => $number) {
            $user = $this->userManager->getRepository()->findOneBy(['username' => $userName]);
            foreach ($this->faker->randomElements($assets, min($number, count($assets))) as $asset) {
                $amount = Money::USD($this->faker->numberBetween(25, 100) * 1000 * 100);
                $position = $this->createPositionInvestment($user, $asset, $amount);
                $list[] = $this->buildFirstContributionEvent($position, $amount);
            }
        }
        $this->messageBus->dispatch(new CreatePositionEventListCommand($list));
    }

    public function getOrder(): int
    {
        return 30;
    }

    protected function buildFirstContributionEvent(Position $position, Money $amount): CreatePositionEventDto
    {
        $dto = new CreatePositionEventDto();
        $dto->value = PositionValueDto::factory($amount);
        $dto->position = $position;
        $dto->date = $this->faker->dateTimeBetween('-5 years', '-2 months');
        $dto->type = PositionEventType::CONTRIBUTION;
        $dto->transactions[] = CreateTransactionDto::factory($amount->multiply(-1));

        return $dto;
    }

    protected function buildDistributionEvent(Position $position, DateTime $date, Money $distributionAmount, bool $isReinvested = false, ?Money $amount = null): CreatePositionEventDto
    {
        $dto = new CreatePositionEventDto();
        $dto->position = $position;
        $dto->date = $date;
        $dto->transactions[] = CreateTransactionDto::factory($distributionAmount);

        if ($isReinvested) {
            $dto->transactions[] = CreateTransactionDto::factory($distributionAmount->multiply(-1));
            $dto->type = PositionEventType::REINVEST;
        } else {
            $dto->type = PositionEventType::DISTRIBUTION;
        }
        if (!is_null($amount)) {
            $dto->value = PositionValueDto::factory($amount);
        }

        return $dto;
    }

    protected function buildContributionEvent(Position $position, DateTime $date, Money $contributionAmount, ?Money $amount = null): CreatePositionEventDto
    {
        $dto = new CreatePositionEventDto();
        $dto->position = $position;
        $dto->date = $date;
        $dto->transactions[] = CreateTransactionDto::factory($contributionAmount->multiply(-1));
        $dto->value = PositionValueDto::factory($amount);
        $dto->type = PositionEventType::CONTRIBUTION;

        return $dto;
    }

    protected function buildCompleteEvent(Position $position, DateTime $date, Money $distributionAmount): CreatePositionEventDto
    {
        $dto = new CreatePositionEventDto();
        $dto->position = $position;
        $dto->date = $date;
        $dto->transactions[] = CreateTransactionDto::factory($distributionAmount);
        $dto->value = PositionValueDto::factory(Money::USD(0));
        $dto->type = PositionEventType::COMPLETE;

        return $dto;
    }

    protected function buildValueUpdateEvent(Position $position, DateTime $date, Money $amount, PositionEventType $type = PositionEventType::VALUE_UPDATE): CreatePositionEventDto
    {
        $dto = new CreatePositionEventDto();
        $dto->position = $position;
        $dto->date = $date;
        $dto->value = PositionValueDto::factory($amount);
        $dto->type = $type;

        return $dto;
    }

    protected function createPositionInvestment(User $user, AssetInvestment $asset, Money $amount): Position
    {
        $dto = new CreatePositionInvestmentDto();
        $dto->capitalCommitment = $amount;
        $dto->createdBy = $user;
        $dto->isDirect = $this->faker->boolean();
        $dto->asset = $asset;
        $dto->tags = $this->fakeTags($user);
        $dto->notes = $this->fakeNotes();
        /* @var PositionInvestment $pos */
        return $this->messageBus->dispatch(new CreatePositionInvestmentCommand($dto))->last(HandledStamp::class)->getResult();
    }

    protected function fakeTags(User $user): array
    {
        return $this->faker->boolean() ? $this->faker->randomElements($this->getTags($user), $this->faker->numberBetween(1, 3)) : [];
    }

    protected function fakeNotes(): ?string
    {
        return $this->faker->boolean() ? $this->faker->text(200) : null;
    }

    /** @return array<Tag> */
    protected function getTags(User $user): array
    {
        $key = strval($user->getId());
        if (!isset($this->tags[$key])) {
            $this->tags[$key] = $this->tagRepository->findBy(['createdBy' => $user]);
        }

        return $this->tags[$key];
    }
}
