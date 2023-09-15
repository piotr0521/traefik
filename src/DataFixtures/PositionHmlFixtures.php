<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use DateInterval;
use DatePeriod;
use DateTime;
use Groshy\Entity\Position;
use Groshy\Message\Command\PositionEvent\CreatePositionEventListCommand;

final class PositionHmlFixtures extends PositionBaseFixtures
{
    public function loadData(): void
    {
        $users = [
            'user1' => $this->faker->numberBetween(3, 7),
            'user2' => $this->faker->numberBetween(3, 7),
            'user7' => $this->faker->numberBetween(2, 5),
            'user14' => 2,
        ];
        $assets = $this->loadInvestments();
        $this->createPositionInvestmentAndEvents($users, $assets);

        $positions = $this->positionInvestmentRepository->findBy(['asset' => $assets]);
        $list = [];
        $isCompletedFound = false;
        /** @var Position $position */
        foreach ($positions as $position) {
            $endDate = null;
            if (!$isCompletedFound && 'user14' == $position->getCreatedBy()->getUsernameCanonical()) {
                $endDate = new DateTime('-1 month');
                $isCompletedFound = true;
            }
            $amount = $position->getLastValue()->getAmount();
            $period = new DatePeriod(
                $position->getStartDate(),
                new DateInterval('P1M'),
                is_null($endDate) ? new DateTime() : $endDate,
                DatePeriod::EXCLUDE_START_DATE
            );
            foreach ($period as $date) {
                // dividend transaction
                $distributionAmount = $amount->multiply(strval($this->faker->randomFloat(4, '0.06', '0.12') / 12));
                $isReinvested = $this->getDividendFlag($position->getCreatedBy());

                if ($isReinvested) {
                    $amount = $amount->add($distributionAmount);
                    $list[] = $this->buildDistributionEvent($position, $date, $distributionAmount, true, $amount);
                } else {
                    $list[] = $this->buildDistributionEvent($position, $date, $distributionAmount);
                }

                $isContribution = 1 == $this->faker->numberBetween(1, 10) && 'user14' == $position->getCreatedBy()->getUsernameCanonical();
                if ($isContribution) {
                    $contributionDate = clone $date;
                    $contributionDate = $contributionDate->add(new DateInterval('P5D'));
                    $contributionAmount = $amount->multiply('0.1');
                    $amount = $amount->add($contributionAmount);
                    $list[] = $this->buildContributionEvent($position, $contributionDate, $contributionAmount, $amount);
                }
            }

            // last distribution for completed investment
            if (!is_null($endDate)) {
                $list[] = $this->buildCompleteEvent($position, $endDate, $amount);
            }
        }

        $this->messageBus->dispatch(new CreatePositionEventListCommand($list));
    }

    private function getDividendFlag($user): bool
    {
        if ('user1' == $user->getUsername()) {
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
            'Hard Money Loan Fund',
        ]])]);
    }
}
