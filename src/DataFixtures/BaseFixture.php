<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

abstract class BaseFixture extends Fixture
{
    protected Generator $faker;

    abstract protected function loadData(): void;

    public function load(ObjectManager $manager)
    {
        // memory optimization
        // https://stackoverflow.com/questions/9699185/memory-leaks-symfony2-doctrine2-exceed-memory-limit
        gc_enable();
        $manager->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->faker = Factory::create();
        $this->loadData();
        $manager->clear();
        gc_collect_cycles();
    }
}
