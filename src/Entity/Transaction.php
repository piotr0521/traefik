<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
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
use Groshy\Model\MoneyAwareTrait;
use Money\Money;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['transaction:item:read'], 'swagger_definition_name' => 'Item Read']),
        new GetCollection(normalizationContext: ['groups' => ['transaction:collection:read'], 'swagger_definition_name' => 'Item Read']), ],
    order: ['transactionDate' => 'DESC'],
    paginationClientItemsPerPage: true
)]
#[Entity]
#[Table(name: 'transaction')]
#[ApiFilter(filterClass: DateFilter::class, properties: ['transactionDate'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['position' => 'exact', 'type' => 'exact', 'transactionDate' => 'exact', 'position.asset.assetType.parent' => 'exact'])]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['transactionDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
class Transaction implements ResourceInterface
{
    use ResourceTrait;
    use MoneyAwareTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['transaction:item:read', 'transaction:collection:read'])]
    protected mixed $id;

    #[Embedded(class: Money::class)]
    protected ?Money $amount = null;

    #[Column(type: 'decimal', precision: 16, scale: 6, nullable: true)]
    #[Groups(['transaction:item:read', 'transaction:collection:read'])]
    protected ?float $quantity = null;

    #[Column(type: 'date')]
    #[Groups(['transaction:item:read', 'transaction:collection:read'])]
    protected ?DateTime $transactionDate = null;

    #[Column(type: 'text', nullable: true)]
    #[Groups(['transaction:item:read', 'transaction:collection:read'])]
    protected ?string $notes = null;

    #[ApiProperty(readableLink: true)]
    #[ManyToOne(targetEntity: PositionEvent::class)]
    #[JoinColumn(name: 'position_event_id', nullable: false)]
    protected ?PositionEvent $positionEvent = null;

    #[ApiProperty(readableLink: true)]
    #[ManyToOne(targetEntity: Position::class)]
    #[JoinColumn(name: 'position_id', nullable: false)]
    #[Groups(['transaction:item:read', 'transaction:collection:read'])]
    #[Context(context: ['groups' => 'position:cascade:read'])]
    protected ?Position $position = null;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    #[Groups(['transaction:item:read', 'transaction:collection:read'])]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    #[Groups(['transaction:item:read'])]
    protected ?DateTime $updatedAt = null;

    public function getAmount(): ?Money
    {
        return $this->amount;
    }

    public function setAmount(Money $amount): void
    {
        $this->amount = $amount;
    }

    #[Groups(['transaction:item:read', 'transaction:collection:read', 'transaction:cascade:read'])]
    #[SerializedName('amount')]
    public function getAmountStruct(): ?array
    {
        return $this->formatMoney($this->amount);
    }

    public function setAmountMinorUnit(int $amount): void
    {
        $this->amount = $this->createMoney($amount);
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getTransactionDate(): ?DateTime
    {
        return $this->transactionDate;
    }

    public function setTransactionDate(DateTime $transactionDate): void
    {
        $this->transactionDate = $transactionDate;
    }

    public function getPositionEvent(): ?PositionEvent
    {
        return $this->positionEvent;
    }

    public function setPositionEvent(PositionEvent $positionEvent): void
    {
        $this->positionEvent = $positionEvent;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position): void
    {
        $this->position = $position;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
}
