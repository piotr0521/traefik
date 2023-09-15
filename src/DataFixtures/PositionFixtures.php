<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use DateInterval;
use DatePeriod;
use DateTime;
use Groshy\Entity\Position;
use Groshy\Message\Command\PositionEvent\CreatePositionEventListCommand;

final class PositionFixtures extends PositionBaseFixtures
{
    public function loadData(): void
    {
        $users = [
            'user1' => $this->faker->numberBetween(3, 7),
            'user2' => $this->faker->numberBetween(3, 7),
            'user4' => $this->faker->numberBetween(3, 7),
            'user6' => $this->faker->numberBetween(3, 7),
            'user7' => $this->faker->numberBetween(30, 40),
            'user13' => $this->faker->numberBetween(30, 40),
        ];
        $assets = $this->loadInvestments();
        $this->createPositionInvestmentAndEvents($users, $assets);
        $positions = $this->positionInvestmentRepository->findBy(['asset' => $assets]);
        $list = [];
        /** @var Position $position */
        foreach ($positions as $position) {
            $amount = $position->getLastValue()->getAmount();
            $interval = date_diff($position->getStartDate(), new DateTime());
            $isCompleted = $interval->days > 500 && $this->faker->boolean();
            $lastDate = $isCompleted ? $this->faker->dateTimeBetween('-1 year', '-1 month') : new DateTime();
            $period = new DatePeriod(
                $position->getStartDate(),
                new DateInterval($this->faker->boolean() ? 'P1M' : 'P3M'),
                $lastDate,
                DatePeriod::EXCLUDE_START_DATE
            );
            foreach ($period as $date) {
                $distributionAmount = $amount->multiply(strval($this->faker->randomFloat(4, '0.03', '0.14') / 12));
                $list[] = $this->buildDistributionEvent($position, $date, $distributionAmount);
            }
            if ($isCompleted) {
                $list[] = $this->buildCompleteEvent($position, $lastDate->add(DateInterval::createFromDateString('1 day')), $amount);
            }
        }
        $this->messageBus->dispatch(new CreatePositionEventListCommand($list));
    }

    private function loadInvestments(): array
    {
        return $this->assetInvestmentRepository->findBy(['assetType' => $this->assetTypeRepository->findBy(['name' => [
            'Search Fund',
            'Life Insurance Settlements',
            'Music Royalties',
            'Private Equity GP Fund',
            'Private Equity LP Fund',
            'Real Estate GP Fund',
            'Real Estate LP Fund',
            'Litigation Financing',
        ]])]);
    }
}
