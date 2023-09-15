<?php

declare(strict_types=1);

namespace Groshy\Model;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Groshy\Entity\AssetPriceHistoryInterface;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

final class AssetListPriceCollection extends ArrayCollection
{
    public function ensureKey(UuidInterface|string $key)
    {
        $strKey = strval($key);
        if (!$this->containsKey($strKey)) {
            $this->set($strKey, new AssetPriceCollection());
        }

        return $this->get($strKey);
    }

    /**
     * @param array<AssetPriceHistoryInterface> $elements
     */
    public static function factory(array $elements): AssetListPriceCollection
    {
        $collection = new AssetListPriceCollection();
        foreach ($elements as $element) {
            Assert::isInstanceOf($element, AssetPriceHistoryInterface::class);
            $collection->ensureKey($element->getAsset()->getId())
                ->set($element->getPricedAt(), $element->getPrice());
        }

        return $collection;
    }

    /**
     * @param array<AssetPriceHistoryInterface> $elements
     */
    public static function factoryForcedDate(array $elements, DateTime $date): AssetListPriceCollection
    {
        $collection = new AssetListPriceCollection();
        foreach ($elements as $element) {
            Assert::isInstanceOf($element, AssetPriceHistoryInterface::class);
            $collection->ensureKey($element->getAsset()->getId())
                ->set($date, $element->getPrice());
        }

        return $collection;
    }

    public function containsKey($key)
    {
        return parent::containsKey(strval($key));
    }

    /**
     * @return ?AssetPriceCollection
     */
    public function get($key)
    {
        return parent::get(strval($key));
    }

    public function merge(AssetListPriceCollection $collection)
    {
        foreach ($collection->getKeys() as $key) {
            $this->ensureKey($key)->merge($collection->get($key));
        }
    }

    public function closeRange(DateTime $lastDate): void
    {
        $this->forAll(fn (string $key, AssetPriceCollection $el) => $el->closeRange($lastDate));
    }
}
