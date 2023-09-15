<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use DateInterval;
use DatePeriod;
use DateTime;
use Groshy\Entity\Position;
use Groshy\Message\Command\PositionEvent\CreatePositionEventListCommand;

final class PositionReitFixtures extends PositionBaseFixtures
{
    public function loadData(): void
    {
        $users = [
            'user1' => $this->faker->numberBetween(5, 10),
            'user2' => $this->faker->numberBetween(1, 2),
            'user4' => $this->faker->numberBetween(1, 2),
            'user7' => $this->faker->numberBetween(1, 2),
            'user9' => $this->faker->numberBetween(1, 2),
            'user14' => 1,
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
                new DateInterval('P1M'),
                $lastDate,
                DatePeriod::EXCLUDE_START_DATE
            );
            foreach ($period as $date) {
                $distributionAmount = $amount->multiply(strval($this->faker->randomFloat(4, '0.03', '0.06') / 12));
                $isReinvested = $this->getDividendFlag($position->getCreatedBy());

                // share price change
                $amount = $amount->multiply(strval(1 + $this->faker->randomFloat(4, '-0.05', '0.15') / 12));
                if ($isReinvested) {
                    $amount = $amount->add($distributionAmount);
                    $list[] = $this->buildDistributionEvent($position, $date, $distributionAmount, true, $amount);
                } else {
                    $list[] = $this->buildDistributionEvent($position, $date, $distributionAmount, false, $amount);
                }
            }
            if ($isCompleted) {
                $list[] = $this->buildCompleteEvent($position, $lastDate->add(DateInterval::createFromDateString('1 day')), $amount);
            }
        }
        $this->messageBus->dispatch(new CreatePositionEventListCommand($list));
    }

    private function getDividendFlag($user): bool
    {
        if ('user1' == $user->getUsername() || 'user14' == $user->getUsername()) {
            return true;
        }
        if ('user2' == $user->getUsername()) {
            return false;
        }

        return $this->faker->boolean;
    }

    private function loadInvestments(): array
    {
        return $this->assetInvestmentRepository->findBy(['assetType' => $this->assetTypeRepository->findBy(['name' => [
            'Public Non Traded REIT',
        ]])]);
    }
}
