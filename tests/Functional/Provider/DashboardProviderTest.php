<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Provider;

use DateTime;
use Groshy\Message\CommandHandler\PositionCash\CreatePositionCashHandler;
use Groshy\Provider\DashboardProvider;
use Groshy\Tests\Helper\DataBuilder;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class DashboardProviderTest extends KernelTestCase
{
    use UsersAwareTrait;
    use DataBuilder;

    private DashboardProvider $provider;
    private CreatePositionCashHandler $handler;
    private RepositoryInterface $accountTypeRepository;
    private RepositoryInterface $assetTypeRepository;

    protected function setUp(): void
    {
        $this->setUpUsers(static::getContainer());
        $this->provider = static::getContainer()->get(DashboardProvider::class);
        $this->handler = static::getContainer()->get(CreatePositionCashHandler::class);
        $this->accountTypeRepository = static::getContainer()->get('app.repository.account_type');
        $this->assetTypeRepository = static::getContainer()->get('app.repository.asset_type');
    }

    /**
     * @test
     */
    public function it_builds_dashboard_data_for_user_without_any_positions(): void
    {
        $result = $this->provider->getDashboardData(
            DateTime::createFromFormat('Y-m-d', '2022-05-08'),
            DateTime::createFromFormat('Y-m-d', '2022-07-01'),
            $this->getUser('user0')
        );
        self::assertEquals(0, $result['stats']['count']);
    }

    /**
     * @test
     */
    public function it_builds_dashboard_data_for_user_with_one_stock_position(): void
    {
        $this->markTestSkipped('Not implemented yet');
        $result = $this->provider->getDashboardData(
            DateTime::createFromFormat('Y-m-d', '2022-05-08'),
            DateTime::createFromFormat('Y-m-d', '2022-07-01'),
            $this->getUser('user15')
        );
        self::assertCount(39, $result['total']['total']['value']['graph']);
        self::assertCount(39, $result['balance'][1]['value']['graph']);
        self::assertCount(0, $result['balance'][0]['value']['graph']);
        self::assertCount(1, $result['position']);
        self::assertCount(1, $result['type']);
        self::assertCount(1, $result['root_type']);
    }

    /**
     * @test
     */
    public function it_builds_dashboard_for_user_with_multiple_positions(): void
    {
        $result = $this->provider->getDashboardData(
            DateTime::createFromFormat('Y-m-d', '2023-04-04'),
            DateTime::createFromFormat('Y-m-d', '2023-05-03'),
            $this->getUser('user2')
        );
        self::assertGreaterThanOrEqual(1, count($result['balance'][0]['value']['graph']));
        self::assertGreaterThanOrEqual(1, count($result['balance'][1]['value']['graph']));
        self::assertGreaterThanOrEqual(1, count($result['position']));
        self::assertGreaterThanOrEqual(1, count($result['type']));
        self::assertGreaterThanOrEqual(1, count($result['root_type']));
    }

    /**
     * @test
     */
    public function it_does_not_change_total_value_based_on_dates(): void
    {
        $dates = [
            new DateTime('- 1 month'),
            new DateTime('- 3 months'),
            new DateTime('- 6 months'),
            new DateTime('- 12 months'),
            DateTime::createFromFormat('Y-m-d', '2022-01-01'),
        ];

        $previousResult = null;
        foreach ($dates as $date) {
            $result = $this->provider->getDashboardData(
                $date,
                new DateTime(),
                $this->getUser('user4')
            );
            if (is_null($previousResult)) {
                $previousResult = $result;
                continue;
            }

            $this->compareArrays($previousResult['position'], $result['position']);
            $this->compareArrays($previousResult['type'], $result['type']);
            $this->compareArrays($previousResult['root_type'], $result['root_type']);

            self::assertTrue(
                $this->areEqualFloats($previousResult['balance']['1']['value']['current'], $result['balance']['1']['value']['current']),
                'Assets are not equal, previous: '.$previousResult['balance']['1']['value']['current'].', current: '.$result['balance']['1']['value']['current']
            );
            self::assertTrue(
                $this->areEqualFloats($previousResult['balance']['0']['value']['current'], $result['balance']['0']['value']['current']),
                'Liabilities are not equal, previous: '.$previousResult['balance']['0']['value']['current'].', current: '.$result['balance']['0']['value']['current']
            );
            self::assertTrue($this->areEqualFloats($previousResult['total']['total']['value']['current'], $result['total']['total']['value']['current']));
        }
    }

    /**
     * @test
     */
    public function it_builds_dashboard_for_position_without_any_data(): void
    {
        $position = $this->createCashPosition($this->getUser('user2'));
        $result = $this->provider->getDashboardData(
            DateTime::createFromFormat('Y-m-d', '2022-01-08'),
            DateTime::createFromFormat('Y-m-d', '2022-07-01'),
            $this->getUser('user2'),
            null,
            $position
        );
        self::assertArrayHasKey('balance', $result);
        self::assertArrayHasKey('total', $result);
        self::assertArrayHasKey('stats', $result);
        self::assertEquals(1, $result['stats']['count']);
    }

    /**
     * @test
     */
    public function it_builds_dashboard_for_credit_card(): void
    {
        $result = $this->provider->getDashboardData(
            new DateTime('-6 months'),
            new DateTime(),
            $this->getUser('user14'),
            $this->assetTypeRepository->findOneBy(['name' => 'Credit Card']),
        );
        self::assertArrayHasKey('stats', $result);
        self::assertEquals(0, $result['stats']['count']);
        self::assertArrayHasKey('dates', $result['stats']);
    }

    private function compareArrays(array $first, array $second)
    {
        foreach ($first as $key => $element) {
            self::assertTrue($this->areEqualFloats($element['value']['current'], $second[$key]['value']['current']), 'Current value does not match for position '.$key);
            self::assertTrue($this->areEqualFloats($element['allocation']['current'], $second[$key]['allocation']['current']), 'Current allocation does not match for position '.$key);
        }
    }

    private function areEqualFloats(float $n1, float $n2): bool
    {
        $epsilon = 0.000001;

        return abs($n1 - $n2) <= $epsilon;
    }
}
