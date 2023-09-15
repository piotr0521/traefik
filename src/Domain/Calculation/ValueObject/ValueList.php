<?php

declare(strict_types=1);

namespace Groshy\Domain\Calculation\ValueObject;

use JsonSerializable;
use Money\Money;
use Webmozart\Assert\Assert;

// Class represents a hash map where key is a string and value is mostly Money but can also be any value
final class ValueList implements JsonSerializable
{
    public function __construct(
        private readonly array $values,
    ) {
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function keyCheckSum(): string
    {
        return md5(implode('', array_keys($this->values)));
    }

    public function jsonSerialize(): array
    {
        return $this->getValues();
    }

    public function add(ValueList $list): ValueList
    {
        return $this->op($list, fn (Money $el1, Money $el2) => $el1->add($el2));
    }

    public function subtract(ValueList $list): ValueList
    {
        return $this->op($list, fn (Money $el1, Money $el2) => $el1->subtract($el2));
    }

    public function multiply(ValueList $list): ValueList
    {
        return $this->op($list, fn (Money $el1, int|string $el2) => $el1->multiply($el2));
    }

    public function checkCompatibility(ValueList $graph): bool
    {
        return $this->keyCheckSum() == $graph->keyCheckSum();
    }

    private function op(ValueList $list, callable $op): ValueList
    {
        Assert::true($list->checkCompatibility($this));
        $keys = array_keys($this->values);

        return new ValueList(
            array_combine(
                $keys,
                array_map(fn (string $key, $value) => $op($this->values[$key], $value), $keys, $list->getValues())
            )
        );
    }
}
