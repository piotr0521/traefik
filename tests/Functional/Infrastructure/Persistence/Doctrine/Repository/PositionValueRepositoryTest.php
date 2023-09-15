<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionValueRepositoryTest extends KernelTestCase
{
    private RepositoryInterface $positionValueRepository;
    private RepositoryInterface $positionRepository;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->positionValueRepository = static::getContainer()->get('app.repository.position_value');
        $this->positionRepository = static::getContainer()->get('app.repository.position');
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * @test
     */
    public function it_returns_list_of_dates_after_defined_date(): void
    {
        $result = $this->getPositionWithMaxValues();
        $position = $this->positionRepository->find($result['position_id']);
        $positionValues = $this->positionValueRepository->findBy(['position' => $position], ['date' => 'ASC']);
        self::assertCount($result['counter'], $positionValues);
        $index = intval($result['counter'] / 2);
        $valuesAfter = $this->positionValueRepository->getDateListAfter($positionValues[$index]->getDate(), $position);
        foreach ($valuesAfter as $pv1) {
            ++$index;
            self::assertEquals($positionValues[$index]->getDate()->format('Y-m-d'), $pv1->format('Y-m-d'));
        }
    }

    private function getPositionWithMaxValues(): array
    {
        $sql = '
            SELECT position_id, COUNT(id) as counter FROM position_value GROUP BY position_id ORDER BY counter DESC LIMIT 1
        ';

        return $this->em->getConnection()->prepare($sql)->executeQuery()->fetchAssociative();
    }
}
