<?php

declare(strict_types=1);

namespace Groshy\Application\QueryHandler;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Groshy\Application\Query\UserGraphQuery;
use Groshy\Domain\Calculation\Graph\GraphBuilder;
use Groshy\Domain\Calculation\ValueObject\DateRange;
use Groshy\Domain\Calculation\ValueObject\ValueList;
use Groshy\Entity\Asset;
use Groshy\Entity\Position;
use Groshy\Entity\PositionValue;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class UserGraphQueryHandler
{
    public function __construct(
        private readonly RepositoryInterface $positionValueRepository,
        private readonly RepositoryInterface $assetSecurityPriceRepository,
        private readonly RepositoryInterface $assetCryptoPriceRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(UserGraphQuery $query): ValueList
    {
        $assets = array_map(fn (Position $value): Asset => $value->getAsset(), $query->positions);
        $graphBuilder = new GraphBuilder(
            new DateRange($query->from, $query->to),
            $query->positions,
            array_merge(
                array_map(
                    fn (PositionValue $pv) => $this->replaceDate($pv, $query->from),
                    $this->positionValueRepository->findLastByPositionsAndBeforeDate($query->positions, $query->from)
                ),
                $this->positionValueRepository->findAllByPositionsAndInterval($query->positions, $query->from, $query->to),
            ),
            array_merge(
                $this->assetSecurityPriceRepository->findLastByAssetsAndBeforeDate($assets, $query->from),
                $this->assetCryptoPriceRepository->findLastByAssetsAndBeforeDate($assets, $query->from),
                $this->assetSecurityPriceRepository->findAllByAssetsAndInterval($assets, $query->from, $query->to),
                $this->assetCryptoPriceRepository->findAllByAssetsAndInterval($assets, $query->from, $query->to)
            )
        );

        return $graphBuilder->build();
    }

    // Detach position value to avoid unintentional update
    private function replaceDate(PositionValue $positionValue, DateTime $date): PositionValue
    {
        $this->em->detach($positionValue);
        $positionValue->setDate($date);

        return $positionValue;
    }
}
