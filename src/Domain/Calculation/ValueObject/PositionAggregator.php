<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\ValueObject;

use Closure;
use Groshy\Entity\Position;

final class PositionAggregator
{
    private array $groupedData = [];

    private Closure $positionKey;

    public function __construct(
        Closure $elementKey,
        Closure $positionKey,
        array $data
    ) {
        foreach ($data as $element) {
            $key = $elementKey($element);
            if (!isset($this->groupedData[$key])) {
                $this->groupedData[$key] = [];
            }
            $this->groupedData[$key][] = $element;
        }
        $this->positionKey = $positionKey;
    }

    public function get(Position $position): array
    {
        $key = $this->positionKey->call($this, $position);
        if (!isset($this->groupedData[$key])) {
            return [];
        }

        return $this->groupedData[$key];
    }
}
