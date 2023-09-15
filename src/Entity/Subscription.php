<?php

declare(strict_types=1);

namespace Groshy\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\Resource\Model\Timestampable;

#[Entity]
#[Table(name: 'billing_subscription')]
class Subscription implements ResourceInterface
{
    use ResourceTrait;
    use Timestampable;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    protected mixed $id;

    #[Column(type: 'string', length: 250, unique: true)]
    protected ?string $stripeId = null;

    #[Column(type: 'string', length: 250)]
    protected ?string $status = null;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    protected ?DateTime $updatedAt = null;

    #[ManyToOne(targetEntity: Customer::class)]
    #[JoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    protected ?Customer $customer = null;

    #[ManyToOne(targetEntity: Price::class)]
    #[JoinColumn(name: 'price_id', referencedColumnName: 'id')]
    protected ?Price $price = null;

    public function getId(): mixed
    {
        return $this->id;
    }

    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(string $stripeId): void
    {
        $this->stripeId = $stripeId;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getPrice(): ?Price
    {
        return $this->price;
    }

    public function setPrice(Price $price): void
    {
        $this->price = $price;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trialing', 'active', 'past_due']);
    }
}
