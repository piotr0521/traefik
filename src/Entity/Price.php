<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Money\Money;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\Resource\Model\Timestampable;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'], normalizationContext: ['groups' => ['price:item:read'], 'swagger_definition_name' => 'Item Read']),
        new GetCollection(normalizationContext: ['groups' => ['price:collection:read'], 'swagger_definition_name' => 'Collection Read']),
    ],
    order: ['amount.amount' => 'ASC']
)]
#[Entity]
#[Table(name: 'billing_price')]
class Price implements ResourceInterface
{
    use ResourceTrait;
    use Timestampable;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['price:item:read', 'price:collection:read'])]
    protected mixed $id;

    #[Column(name: 'is_active', type: 'boolean')]
    protected bool $isActive = false;

    #[Embedded(class: Money::class)]
    #[Groups(['price:item:read', 'price:collection:read'])]
    protected ?Money $amount = null;

    #[Column(type: 'string', length: 250)]
    #[Groups(['price:item:read', 'price:collection:read'])]
    protected ?string $stripeId = null;

    #[Column(type: 'string', length: 250)]
    #[Groups(['price:item:read', 'price:collection:read'])]
    protected ?string $recurringInterval = null;

    #[Column(type: 'smallint')]
    #[Groups(['price:item:read', 'price:collection:read'])]
    protected ?int $recurringIntervalCount = null;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    protected ?DateTime $updatedAt = null;

    #[ManyToOne(targetEntity: Product::class, inversedBy: 'prices')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected ?Product $product = null;

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(string $stripeId): void
    {
        $this->stripeId = $stripeId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getAmount(): ?Money
    {
        return $this->amount;
    }

    public function setAmount(?Money $amount): void
    {
        $this->amount = $amount;
    }

    public function getRecurringInterval(): ?string
    {
        return $this->recurringInterval;
    }

    public function setRecurringInterval(?string $recurringInterval): void
    {
        $this->recurringInterval = $recurringInterval;
    }

    public function getRecurringIntervalCount(): ?int
    {
        return $this->recurringIntervalCount;
    }

    public function setRecurringIntervalCount(?int $recurringIntervalCount): void
    {
        $this->recurringIntervalCount = $recurringIntervalCount;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
        $product->addPrice($this);
    }
}
