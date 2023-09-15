<?php

declare(strict_types=1);

namespace Groshy\Application\Manager;

use Talav\Component\Registry\Registry\ServiceRegistryInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

class PositionNormalizer
{
    public function __construct(
        private readonly ServiceRegistryInterface $managerRegistry,
    ) {
    }

    public function normalize(array $criteria, string $className, int $positionChange): void
    {
        /** @var ManagerInterface $manager */
        $manager = $this->managerRegistry->get($className);
        $entities = $manager->getRepository()->findBy($criteria, ['position' => 'ASC', 'updatedAt' => $positionChange < 0 ? 'DESC' : 'ASC']);
        foreach ($entities as $key => $entity) {
            $entity->setPosition($key);
        }
        $manager->flush();
    }
}
