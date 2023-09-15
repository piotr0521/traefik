<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Message\Command\PositionEvent\CreatePositionEventCommand;
use Groshy\Message\Command\PositionEvent\DeletePositionEventCommand;
use Groshy\Message\Command\PositionEvent\UpdatePositionEventCommand;
use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\UpdatePositionEventDto;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Presentation\Api\Dto\PositionEvent\ApiCreatePositionEventDto;
use Groshy\Presentation\Api\Dto\PositionEvent\ApiUpdatePositionEventDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Money\Money;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;

// Event encapsulates a set of transactions and value updates.
#[Entity]
#[Table(name: 'position_event')]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['positionEvent:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(input: ['class' => ApiUpdatePositionEventDto::class, 'transform' => ['dto' => UpdatePositionEventDto::class, 'command' => UpdatePositionEventCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(input: ['transform' => ['command' => DeletePositionEventCommand::class]], processor: ResourceStateProcessor::class),
        new Post(input: ['class' => ApiCreatePositionEventDto::class, 'transform' => ['dto' => CreatePositionEventDto::class, 'command' => CreatePositionEventCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(normalizationContext: ['groups' => ['positionEvent:collection:read'], 'swagger_definition_name' => 'Item Read']), ],
    order: ['date' => 'DESC'],
    paginationClientItemsPerPage: true
)]
#[ApiFilter(filterClass: DateFilter::class, properties: ['date'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['position' => 'exact', 'type' => 'exact', 'date' => 'exact', 'position.asset.assetType.parent' => 'exact'])]
#[ApiFilter(filterClass: BooleanFilter::class, properties: ['type.isBalance'])]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['date' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
class PositionEvent implements ResourceInterface
{
    use ResourceTrait;
    use MoneyAwareTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['positionEvent:item:read', 'positionEvent:collection:read'])]
    protected mixed $id;

    #[Column(type: 'date')]
    #[Groups(['positionEvent:item:read', 'positionEvent:collection:read'])]
    protected ?DateTime $date = null;

    #[Column(type: 'text', nullable: true)]
    #[Groups(['positionEvent:item:read', 'positionEvent:collection:read'])]
    protected ?string $notes = null;

    #[ApiProperty(readableLink: true)]
    #[ManyToOne(targetEntity: Position::class)]
    #[JoinColumn(name: 'position_id', nullable: false)]
    #[Groups(['positionEvent:item:read', 'positionEvent:collection:read'])]
    #[Context(context: ['groups' => 'position:cascade:read'])]
    protected ?Position $position = null;

    #[ApiProperty(readableLink: true)]
    #[OneToOne(mappedBy: 'positionEvent', targetEntity: PositionValue::class, cascade: ['remove'])]
    #[Groups(['positionEvent:item:read', 'positionEvent:collection:read'])]
    #[Context(context: ['groups' => 'positionValue:cascade:read'])]
    protected ?PositionValue $value = null;

    #[ApiProperty(readableLink: true)]
    #[OneToMany(mappedBy: 'positionEvent', targetEntity: Transaction::class, cascade: ['remove'])]
    #[Groups(['positionEvent:item:read'])]
    #[Context(context: ['groups' => ['transaction:cascade:read']])]
    protected Collection $transactions;

    #[Column(type: 'string', enumType: PositionEventType::class)]
    #[Groups(['positionEvent:item:read', 'positionEvent:collection:read', 'positionEvent:cascade:read'])]
    protected ?PositionEventType $type = null;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    #[Groups(['positionEvent:item:read', 'positionEvent:collection:read'])]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    #[Groups(['positionEvent:item:read'])]
    protected ?DateTime $updatedAt = null;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(?DateTime $date): void
    {
        $this->date = $date;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(Position $position): void
    {
        $this->position = $position;
    }

    public function getType(): ?PositionEventType
    {
        return $this->type;
    }

    public function setType(PositionEventType $type): void
    {
        $this->type = $type;
    }

    public function getValue(): ?PositionValue
    {
        return $this->value;
    }

    public function setValue(PositionValue $value): void
    {
        $this->value = $value;
        if ($value->getPositionEvent() !== $this) {
            $value->setPositionEvent($this);
        }
    }

    public function removeValue(): void
    {
        $this->value = null;
    }

    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function getPositiveTransactions(): Collection
    {
        return $this->transactions->filter(fn (Transaction $el) => $el->getAmount()->isPositive());
    }

    public function getNegativeTransactions(): Collection
    {
        return $this->transactions->filter(fn (Transaction $el) => $el->getAmount()->isNegative());
    }

    public function addTransaction(Transaction $transaction): void
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
        }
        if ($transaction->getPositionEvent() !== $this) {
            $transaction->setPositionEvent($this);
        }
    }

    public function removeTransaction(Transaction $transaction): void
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
        }
    }

    public function getCashIn(): Money
    {
        return Money::USD(0)->add(...$this->getNegativeTransactions()->map(fn (Transaction $el) => $el->getAmount())->toArray());
    }

    #[Groups(['positionEvent:item:read', 'positionEvent:collection:read'])]
    #[SerializedName('cashIn')]
    public function getCashInStruct(): ?array
    {
        return $this->getCashIn()->isZero() ? null : $this->formatMoney($this->getCashIn());
    }

    public function getCashOut(): Money
    {
        return Money::USD(0)->add(...$this->getPositiveTransactions()->map(fn (Transaction $el) => $el->getAmount())->toArray());
    }

    #[Groups(['positionEvent:item:read', 'positionEvent:collection:read'])]
    #[SerializedName('cashOut')]
    public function getCashOutStruct(): ?array
    {
        return $this->getCashOut()->isZero() ? null : $this->formatMoney($this->getCashOut());
    }
}
