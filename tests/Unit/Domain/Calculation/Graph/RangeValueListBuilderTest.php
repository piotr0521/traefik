<?php

declare(strict_types=1);

namespace Groshy\Tests\Unit\Domain\Calculation\Graph;

use DateTime;
use Groshy\Domain\Calculation\Graph\RangeValueListBuilder;
use Groshy\Domain\Calculation\ValueObject\DateRange;
use Groshy\Domain\Calculation\ValueObject\DateValueAwareInterface;
use PHPUnit\Framework\TestCase;

final class RangeValueListBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_all_keys_with_null_values_if_there_are_no_values()
    {
        $builder = new RangeValueListBuilder(new DateRange(new DateTime('-1 day'), new DateTime()));
        $values = $builder->build()->getValues();
        self::assertCount(2, $values);
        self::assertEquals(0, reset($values));
    }

    /**
     * @test
     */
    public function it_builds_the_full_range_of_values()
    {
        $builder = new RangeValueListBuilder(new DateRange(new DateTime('2023-05-05'), new DateTime('2023-05-10')));
        $builder->add([
            new RangeValueListBuilderTestModel(new DateTime('2023-05-05'), 10),
            new RangeValueListBuilderTestModel(new DateTime('2023-05-06'), 11),
            new RangeValueListBuilderTestModel(new DateTime('2023-05-07'), 12),
        ]);
        $values = $builder->build()->getValues();
        $expected = [
            '2023-05-05' => 10,
            '2023-05-06' => 11,
            '2023-05-07' => 12,
            '2023-05-08' => 12,
            '2023-05-09' => 12,
            '2023-05-10' => 12,
        ];
        self::assertCount(count($expected), $values);
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $values);
            self::assertEquals($value, $values[$key]);
        }
    }
}

class RangeValueListBuilderTestModel implements DateValueAwareInterface
{
    public function __construct(private readonly DateTime $date, private readonly int $value)
    {
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
