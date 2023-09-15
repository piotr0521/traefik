<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Infrastructure\Persistence\Doctrine\Repository;

use DateTime;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class TransactionRepositoryTest extends KernelTestCase
{
    use UsersAwareTrait;

    private RepositoryInterface $transactionRepository;
    private RepositoryInterface $positionRepository;

    protected function setUp(): void
    {
        $this->transactionRepository = static::getContainer()->get('app.repository.transaction');
        $this->positionRepository = static::getContainer()->get('app.repository.position');
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_returns_result_by_position_and_interval(): void
    {
        $from = new DateTime('-10 years');
        $to = new DateTime();
        $user = $this->getUser('user2');
        $positions = $this->positionRepository->findBy(['createdBy' => $user]);
        $result = $this->transactionRepository->findByPositionsAndInterval($positions, $from, $to);
        self::assertGreaterThanOrEqual(1, count($result));
    }
}
