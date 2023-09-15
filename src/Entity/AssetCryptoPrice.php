<?php

declare(strict_types=1);

namespace Groshy\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Model\MoneyAwareTrait;
use Money\Money;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Talav\Component\Resource\Model\Creatable;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;

#[Entity]
#[UniqueConstraint(columns: ['priced_at', 'asset_id'])]
class AssetCryptoPrice implements ResourceInterface, AssetPriceHistoryInterface
{
    use ResourceTrait;
    use Creatable;
    use MoneyAwareTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    protected mixed $id;

    #[Column(name: 'priced_at', type: 'date')]
    protected ?DateTime $pricedAt = null;

    #[Embedded(class: Money::class)]
    protected ?Money $price = null;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    protected ?DateTime $createdAt = null;

    #[ManyToOne(targetEntity: AssetCrypto::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected ?AssetCrypto $asset = null;

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getPricedAt(): ?DateTime
    {
        return $this->pricedAt;
    }

    public function setPricedAt(DateTime $pricedAt): void
    {
        $this->pricedAt = $pricedAt;
    }

    public function getAsset(): ?AssetCrypto
    {
        return $this->asset;
    }

    public function setAsset(AssetCrypto $asset): void
    {
        $this->asset = $asset;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function setPriceMinorUnit(int $price): void
    {
        $this->price = $this->createMoney($price);
    }

    public function setPriceBaseUnit(float|string $price): void
    {
        $this->price = $this->parseMoney($price);
    }
}
