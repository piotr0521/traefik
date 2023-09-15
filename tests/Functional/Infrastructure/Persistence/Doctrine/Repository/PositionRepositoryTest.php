<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionRepositoryTest extends KernelTestCase
{
    use UsersAwareTrait;

    private RepositoryInterface $positionRepository;
    private RepositoryInterface $assetTypeRepository;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->positionRepository = static::getContainer()->get('app.repository.position');
        $this->assetTypeRepository = static::getContainer()->get('app.repository.asset_type');
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_returns_list_of_positions_ids_and_asset_ids(): void
    {
        $from = new DateTime('-1 month');
        $to = new DateTime();
        $user = $this->getUser('user2');
        $result1 = $this->positionRepository->getByInterval($from, $to, $user);

        $assetType = $this->assetTypeRepository->findAll()[0];
        $result2 = $this->positionRepository->getByInterval($from, $to, $user, $assetType);

        // it should be a smaller number of results with additional filter
        self::assertLessThanOrEqual(count($result1), count($result2));
    }

    /**
     * @test
     */
    public function it_returns_list_of_positions_for_the_same_asset_type(): void
    {
        $from = new DateTime('-1 month');
        $to = new DateTime();
        $user = $this->getUser('user2');
        $assetTypes = $this->assetTypeRepository->findBy(['parent' => null]);
        foreach ($assetTypes as $assetType) {
            $result = $this->positionRepository->getByInterval($from, $to, $user, $assetType);
            foreach ($this->positionRepository->findBy(['id' => $result]) as $position) {
                self::assertEquals($assetType, $position->getAsset()->getRootAssetType());
            }
        }
    }
}
