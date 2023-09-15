<?php

declare(strict_types=1);

namespace Groshy\Model;

final class AttributeTracker
{
    private array $valueGraph = [];

    private array $allocationGraph = [];

    private array $dashData = [];

    public function __construct(
        private readonly AttributeModel $model
    ) {
    }

    public function add(string $date, float $change): void
    {
        $currentValue = (0 == count($this->valueGraph)) ? 0 : end($this->valueGraph);
        $this->valueGraph[$date] = $currentValue + $change;
    }

    public function build(array $totalGraph): void
    {
        if ($this->model->trackable) {
            $prev = 0;
            foreach ($totalGraph as $date => $value) {
                if (0 == $value) {
                    $this->allocationGraph[$date] = 0;
                } else {
                    $curValue = $this->valueGraph[$date] ?? $prev;
                    $this->allocationGraph[$date] = $curValue / $value * 100;
                    $prev = $curValue;
                }
            }
            $allocation = $this->getGraphData($this->allocationGraph);
        } else {
            $allocation = [];
        }

        $this->dashData = [
            'model' => ['id' => $this->model->id, 'name' => $this->model->name],
            'value' => $this->getGraphData($this->valueGraph),
            'allocation' => $allocation,
        ];
    }

    public function getValue(): array
    {
        return $this->getGraphData($this->valueGraph);
    }

    public function getData(): array
    {
        return $this->dashData;
    }

    public function getModel(): AttributeModel
    {
        return $this->model;
    }

    private function getGraphData(array $graph): array
    {
        if (0 == count($graph)) {
            return $this->getGraphEmptyResponse();
        }
        $first = reset($graph);
        $last = end($graph);

        return [
            'current' => $last,
            'graph' => $graph,
            'change' => [
                'amount' => $last - $first,
                'percent' => 0 == $first ? null : ($last - $first) / $first * 100,
            ],
        ];
    }

    private function getGraphEmptyResponse(): array
    {
        return [
            'current' => 0,
            'graph' => [],
            'change' => [
                'amount' => 0,
                'percent' => 0,
            ],
        ];
    }
}
