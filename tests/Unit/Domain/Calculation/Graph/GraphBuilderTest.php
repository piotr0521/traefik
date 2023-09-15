<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Domain\Calculation\Graph;

use DateTime;
use Groshy\Domain\Calculation\Graph\GraphBuilder;
use Groshy\Domain\Calculation\ValueObject\DateRange;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Tests\Helper\ModelBuilder;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class GraphBuilderTest extends TestCase
{
    use ModelBuilder;

    /**
     * @test
     */
    public function it_correctly_handles_gaps_in_data()
    {
        $position = $this->buildPosition(
            positionId: Uuid::uuid4(),
            asset: $this->buildAsset(
                assetType: $this->buildAssetType(
                    isAsset: true,
                    isQuantity: false
                )
            )
        );
        $value1 = $this->buildPositionEvent(
            position: $position,
            date: new DateTime('2023-05-05'),
            valueAmount: Money::USD(100),
            type: PositionEventType::VALUE_UPDATE,
        )->getValue();
        $value2 = $this->buildPositionEvent(
            position: $position,
            date: new DateTime('2023-05-06'),
            valueAmount: Money::USD(200),
            type: PositionEventType::VALUE_UPDATE,
        )->getValue();
        $graph = new GraphBuilder(
            range: new DateRange(new DateTime('2023-05-05'), new DateTime('2023-05-07')),
            positions: [$position],
            positionValues: [$value1, $value2],
            assetPrices: []
        );
        $graphVales = $graph->build()->getValues();
        $expected = [
            '2023-05-05' => Money::USD(100),
            '2023-05-06' => Money::USD(200),
            '2023-05-07' => Money::USD(200),
        ];
        self::assertCount(count($expected), $graphVales);
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $graphVales);
            self::assertTrue($graphVales[$key]->equals($value));
        }
    }

    /**
     * @test
     */
    public function it_subtracts_liabilities()
    {
        $positionAsset = $this->buildPosition(
            positionId: Uuid::uuid4(),
            asset: $this->buildAsset(
                assetType: $this->buildAssetType(
                    isAsset: true,
                )
            )
        );
        $positionLiability = $this->buildPosition(
            positionId: Uuid::uuid4(),
            asset: $this->buildAsset(
                assetType: $this->buildAssetType(
                    isAsset: false,
                )
            )
        );
        $value1 = $this->buildPositionEvent(
            position: $positionAsset,
            date: new DateTime('2023-05-05'),
            valueAmount: Money::USD(100),
            type: PositionEventType::VALUE_UPDATE,
        )->getValue();
        $value2 = $this->buildPositionEvent(
            position: $positionLiability,
            date: new DateTime('2023-05-06'),
            valueAmount: Money::USD(50),
            type: PositionEventType::VALUE_UPDATE,
        )->getValue();
        $graph = new GraphBuilder(
            range: new DateRange(new DateTime('2023-05-05'), new DateTime('2023-05-06')),
            positions: [$positionAsset, $positionLiability],
            positionValues: [$value1, $value2],
            assetPrices: []
        );
        $graphVales = $graph->build()->getValues();
        $expected = [
            '2023-05-05' => Money::USD(100),
            '2023-05-06' => Money::USD(50),
        ];
        self::assertCount(count($expected), $graphVales);
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $graphVales);
            self::assertTrue($graphVales[$key]->equals($value));
        }
    }
}
