<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Domain\Entity\PositionAwareInterface;
use Groshy\Model\MoneyAwareTrait;
use Money\Money;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Webmozart\Assert\Assert;

#[Entity]
#[Table(name: 'position_value')]
#[UniqueConstraint(columns: ['position_event_id'])]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['positionValue:item:read'], 'swagger_definition_name' => 'Item Read']),
    ]
)]
class PositionValue implements ResourceInterface, PositionAwareInterface
{
    use ResourceTrait;
    use MoneyAwareTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['positionValue:item:read', 'positionValue:cascade:read'])]
    protected mixed $id;

    #[Embedded(class: Money::class)]
    protected ?Money $amount = null;

    #[Column(type: 'decimal', precision: 16, scale: 6, nullable: true)]
    #[Groups(['positionValue:item:read', 'positionValue:cascade:read'])]
    protected ?float $quantity = null;

    #[OneToOne(targetEntity: PositionEvent::class)]
    #[JoinColumn(name: 'position_event_id')]
    protected ?PositionEvent $positionEvent = null;

    #[ApiProperty(readableLink: true)]
    #[ManyToOne(targetEntity: Position::class)]
    #[JoinColumn(name: 'position_id', nullable: false)]
    #[Groups(['positionValue:item:read', 'positionValue:collection:read'])]
    #[Context(context: ['groups' => 'position:cascade:read'])]
    protected ?Position $position = null;

    #[Column(type: 'date')]
    #[Groups(['positionValue:item:read', 'positionValue:collection:read'])]
    protected ?DateTime $date = null;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    #[Groups(['positionValue:item:read', 'positionValue:cascade:read'])]
    protected ?DateTime $updatedAt = null;

    public function getAmount(): ?Money
    {
        return $this->amount;
    }

    public function setAmount(?Money $amount): void
    {
        $this->amount = $amount;
    }

    #[Groups(['positionValue:item:read', 'positionValue:cascade:read'])]
    #[SerializedName('amount')]
    public function getAmountStruct(): ?array
    {
        return $this->formatMoney($this->amount);
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): void
    {
        Assert::nullOrGreaterThan($quantity, 0.0);
        $this->quantity = $quantity;
    }

    public function getPositionEvent(): ?PositionEvent
    {
        return $this->positionEvent;
    }

    public function setPositionEvent(?PositionEvent $positionEvent): void
    {
        $this->positionEvent = $positionEvent;
        if ($positionEvent->getValue() !== $this) {
            $positionEvent->setValue($this);
        }
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position): void
    {
        $this->position = $position;
    }

    public function isLastPositionValue(): bool
    {
        $position = $this->getPosition();
        if (is_null($position) || is_null($position->getLastValue())) {
            return false;
        }
        if (!is_null($this->getId())) {
            return $this->getId() == $position->getLastValue()->getId();
        }

        return $this === $position->getLastValue();
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }
}
