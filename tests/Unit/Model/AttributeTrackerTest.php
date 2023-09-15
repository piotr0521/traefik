<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Model;

use Groshy\Model\AttributeModel;
use Groshy\Model\AttributeTracker;
use PHPUnit\Framework\TestCase;

final class AttributeTrackerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_correct_structure_for_zero_values()
    {
        $model = new AttributeModel('type', 'id', 'name');
        $tracker = new AttributeTracker($model);
        $tracker->build(['2022-06-01' => 100]);
        $data = $tracker->getData();
        $this->validateStructure($data);
    }

    /**
     * @test
     */
    public function it_returns_correct_structure_for_one_value()
    {
        $model = new AttributeModel('type', 'id', 'name');
        $tracker = new AttributeTracker($model);
        $tracker->add('2022-06-01', 100);
        $tracker->build(['2022-06-01' => 100]);
        $data = $tracker->getData();
        $this->validateStructure($data);
    }

    /**
     * @test
     */
    public function it_does_not_calculate_allocation_for_non_trackable_models()
    {
        $model = new AttributeModel('type', 'id', 'name', false);
        $tracker = new AttributeTracker($model);
        $tracker->add('2022-06-01', 100);
        $tracker->build(['2022-06-01' => 100]);
        $data = $tracker->getData();
        self::assertCount(0, $data['allocation']);
    }

    /**
     * @test
     */
    public function it_correctly_calculates_change_and_current_value()
    {
        $tracker = new AttributeTracker(new AttributeModel('type', 'id', 'name'));
        $tracker->add('2022-06-01', 200);
        $tracker->add('2022-07-01', 100);
        $tracker->build(['2022-06-01' => 200, '2022-07-01' => 300]);
        $data = $tracker->getData()['value'];
        self::assertEquals(300, $data['current']);
        self::assertEquals(100, $data['change']['amount']);
        self::assertEquals(50, $data['change']['percent']);
    }

    /**
     * @test
     */
    public function it_adds_all_values_to_graph()
    {
        $tracker = new AttributeTracker(new AttributeModel('type', 'id', 'name'));
        $tracker->add('2022-06-01', 200);
        $tracker->add('2022-07-01', 100);
        $tracker->build(['2022-06-01' => 200, '2022-07-01' => 300]);
        $data = $tracker->getData()['value']['graph'];
        self::assertCount(2, $data);
        self::assertArrayHasKey('2022-06-01', $data);
        self::assertArrayHasKey('2022-07-01', $data);
        self::assertEquals(200, $data['2022-06-01']);
        self::assertEquals(300, $data['2022-07-01']);
    }

    /**
     * @test
     */
    public function it_calculates_allocation()
    {
        $tracker = new AttributeTracker(new AttributeModel('type', 'id', 'name'));
        $tracker->add('2022-06-01', 200);
        $tracker->add('2022-07-01', 100);
        $tracker->build(['2022-06-01' => 200, '2022-07-01' => 600, '2022-08-01' => 1000]);
        $data = $tracker->getData()['allocation']['graph'];
        self::assertCount(3, $data);
        self::assertArrayHasKey('2022-06-01', $data);
        self::assertArrayHasKey('2022-07-01', $data);
        self::assertArrayHasKey('2022-08-01', $data);
        self::assertEquals(100, $data['2022-06-01']);
        self::assertEquals(50, $data['2022-07-01']);
        self::assertEquals(30, $data['2022-08-01']);

        $change = $tracker->getData()['allocation']['change'];
        self::assertEquals(-70, $change['amount']);
        self::assertEquals(-70, $change['percent']);
    }

    /**
     * @test
     */
    public function it_calculates_allocation_when_tracker_added_int_the_middle_of_the_time_range()
    {
        $tracker = new AttributeTracker(new AttributeModel('type', 'id', 'name'));
        $tracker->add('2022-06-01', 200);
        $tracker->add('2022-07-01', 100);
        $tracker->build(['2022-05-01' => 200, '2022-06-01' => 400, '2022-07-01' => 1000]);
        $data = $tracker->getData()['allocation']['graph'];
        self::assertCount(3, $data);
        self::assertArrayHasKey('2022-05-01', $data);
        self::assertArrayHasKey('2022-06-01', $data);
        self::assertArrayHasKey('2022-07-01', $data);
        self::assertEquals(0, $data['2022-05-01']);
        self::assertEquals(50, $data['2022-06-01']);
        self::assertEquals(30, $data['2022-07-01']);

        $change = $tracker->getData()['allocation']['change'];
        self::assertEquals(30, $change['amount']);
        self::assertEquals(null, $change['percent']);
    }

    /**
     * @test
     */
    public function it_calculates_allocation_when_value_starts_from_zero()
    {
        $tracker = new AttributeTracker(new AttributeModel('type', 'id', 'name'));
        $tracker->add('2022-06-01', 0);
        $tracker->add('2022-07-01', 100);
        $tracker->build(['2022-06-01' => 0, '2022-07-01' => 100]);
        $data = $tracker->getData()['allocation']['graph'];
        self::assertCount(2, $data);
        self::assertArrayHasKey('2022-06-01', $data);
        self::assertArrayHasKey('2022-07-01', $data);
    }

    private function validateStructure(array $data)
    {
        self::assertArrayHasKey('model', $data);
        self::assertArrayHasKey('value', $data);
        self::assertArrayHasKey('allocation', $data);

        self::assertArrayHasKey('id', $data['model']);
        self::assertArrayHasKey('name', $data['model']);

        self::assertArrayHasKey('current', $data['value']);
        self::assertArrayHasKey('graph', $data['value']);
        self::assertArrayHasKey('change', $data['value']);
        self::assertArrayHasKey('amount', $data['value']['change']);
        self::assertArrayHasKey('percent', $data['value']['change']);

        self::assertArrayHasKey('current', $data['allocation']);
        self::assertArrayHasKey('graph', $data['allocation']);
        self::assertArrayHasKey('change', $data['allocation']);
        self::assertArrayHasKey('amount', $data['allocation']['change']);
        self::assertArrayHasKey('percent', $data['allocation']['change']);
    }
}
