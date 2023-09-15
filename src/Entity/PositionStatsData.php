<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ArrayIterator;
use CallbackFilterIterator;
use Iterator;
use IteratorAggregate;
use Money\Money;

class PositionStatsData implements IteratorAggregate
{
    public function __construct(
        public ?int $count = null,
        public ?int $active = null,
        public ?int $notStarted = null,
        public ?int $completed = null,
        public ?int $new = null,
        public ?Money $capitalCommitted = null,
        public ?Money $capitalCalled = null,
        public ?float $capitalCalledPercent = null,
    ) {
    }

    public function getIterator(): Iterator
    {
        return new CallbackFilterIterator(new ArrayIterator($this), fn ($el) => !is_null($el));
    }

    public function merge(PositionStatsData $data): PositionStatsData
    {
        foreach ($data as $key => $value) {
            if (is_null($this->{$key})) {
                $this->{$key} = $value;
                continue;
            }
            if ($this->{$key} instanceof Money) {
                $this->{$key} = $this->{$key}->add($value);
            } else {
                $this->{$key} = $this->{$key} + $value;
            }
        }

        return $this;
    }
}
