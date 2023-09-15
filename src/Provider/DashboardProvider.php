<?php

declare(strict_types=1);

namespace Groshy\Provider;

use AutoMapperPlus\AutoMapperInterface;
use DateTime;
use Groshy\Entity\AssetType;
use Groshy\Entity\Position;
use Groshy\Model\Dashboard;
use Groshy\Model\DashboardValue;
use Groshy\Model\MoneyAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Talav\Component\User\Model\UserInterface;

final class DashboardProvider
{
    use MoneyAwareTrait;

    public function __construct(
        private readonly RepositoryInterface $positionRepository,
        private readonly PositionStatsFactoryDeprecated $statsFactory,
        private readonly PositionDateCollectionFactory $collectionFactory,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function getDashboardData(DateTime $from, DateTime $to, UserInterface $user, ?AssetType $type = null, ?Position $position = null): array
    {
        $positions = !is_null($position) ? [$position] : $this->positionRepository->getByInterval($from, $to, $user, $type);

        $collection = $this->collectionFactory->build($positions, $from, $to);
        if (is_null($collection)) {
            $result = Dashboard::toDashData([]);
            $result['stats'] = [
                'count' => 0,
                'dates' => [
                    (new DateTime())->format(DATE_ATOM),
                    (new DateTime())->format(DATE_ATOM),
                ],
            ];
        } else {
            $result = Dashboard::toDashData(
                $this->mapper->mapMultiple(iterator_to_array($collection->getPositionDates(), false), DashboardValue::class)
            );
            $result['stats'] = $this->statsFactory->build($collection, $from, $to);
        }

        return $result;
    }
}
