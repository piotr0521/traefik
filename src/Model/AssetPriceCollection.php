<?php

declare(strict_types=1);

namespace Groshy\Model;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use LogicException;
use Money\Money;
use Webmozart\Assert\Assert;

final class AssetPriceCollection extends ArrayCollection
{
    public function set($key, $value)
    {
        Assert::isInstanceOf($value, Money::class);
        if ($key instanceof DateTime) {
            $key = $key->format('Y-m-d');
        }
        parent::set($key, $value);
    }

    public function containsKey($key)
    {
        if ($key instanceof DateTime) {
            $key = $key->format('Y-m-d');
        }

        return parent::containsKey($key);
    }

    /**
     * @return ?Money
     */
    public function get($key)
    {
        if ($key instanceof DateTime) {
            $key = $key->format('Y-m-d');
        }

        return parent::get($key);
    }

    public function merge(AssetPriceCollection $collection)
    {
        foreach ($collection->getKeys() as $key) {
            $this->set($key, $collection->get($key));
        }
    }

    // Make sure that last date is set for price collection
    public function closeRange(DateTime $lastDate): bool
    {
        $key = $lastDate->format('Y-m-d');
        if ($this->containsKey($key)) {
            return true;
        }
        $max = max($this->getKeys());
        if ($max > $key) {
            throw new LogicException('Provided end of range '.$key.' is less than current max '.$max);
        }
        $this->set($key, $this->get($max));

        return true;
    }
}
