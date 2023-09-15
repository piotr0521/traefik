<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Money\Money;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\Resource\Model\Timestampable;
use Talav\Component\User\Model\CreatedBy;
use Talav\Component\User\Model\UserInterface;
use Webmozart\Assert\Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/positions/{id}.{_format}',
            normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']
        ),
        new GetCollection(
            uriTemplate: '/positions.{_format}',
            normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']
        ),
    ],
    routePrefix: '/position'
)]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
#[ApiFilter(filterClass: DateFilter::class, properties: ['startDate' => DateFilter::INCLUDE_NULL_BEFORE_AND_AFTER, 'completeDate' => DateFilter::INCLUDE_NULL_BEFORE_AND_AFTER])]
#[Entity]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[DiscriminatorMap([
    'investment' => 'Groshy\\Entity\\PositionInvestment',
    'cash' => 'Groshy\\Entity\\PositionCash',
    'credit_card' => 'Groshy\\Entity\\PositionCreditCard',
    'property' => 'Groshy\\Entity\\PositionProperty',
    'collectable' => 'Groshy\\Entity\\PositionCollectable',
    'certificate_deposit' => 'Groshy\\Entity\\PositionCertificateDeposit',
    'security' => 'Groshy\\Entity\\PositionSecurity',
    'business' => 'Groshy\\Entity\\PositionBusiness',
    'mortgage' => 'Groshy\\Entity\\PositionMortgage',
    'crypto' => 'Groshy\\Entity\\PositionCrypto',
    'loan' => 'Groshy\\Entity\\PositionLoan',
])]
#[Table(name: 'position')]
class Position implements ResourceInterface, PositionStatsInterface
{
    use ResourceTrait;
    use Timestampable;
    use CreatedBy;
    use MoneyAwareTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['position:item:read', 'position:collection:read', 'position:cascade:read'])]
    protected mixed $id;

    #[Column(type: 'string', length: 250, nullable: true)]
    protected ?string $name = null;

    #[Column(type: 'string', length: 250, nullable: true)]
    #[Groups(['position:item:read'])]
    protected ?string $notes = null;

    #[Column(type: 'date', nullable: true)]
    #[Groups(['position:item:read', 'position:collection:read'])]
    protected ?DateTime $startDate = null;

    #[Column(type: 'date', nullable: true)]
    #[Groups(['position:item:read', 'position:collection:read'])]
    protected ?DateTime $completeDate = null;

    #[Embedded(class: Money::class)]
    protected Money $distributions;

    #[Embedded(class: Money::class)]
    protected Money $contributions;

    #[Column(type: 'string', length: 20)]
    #[Groups(['position:item:read', 'position:collection:read'])]
    protected string $irr = '0.00';

    #[Column(type: 'string', length: 20)]
    #[Groups(['position:item:read', 'position:collection:read'])]
    protected string $multiplier = '1';

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    #[Groups(['position:item:read'])]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    #[Groups(['position:item:read'])]
    protected ?DateTime $updatedAt = null;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[Groups(['position:item:read', 'position:collection:read', 'position:cascade:read'])]
    protected ?UserInterface $createdBy = null;

    #[ManyToOne(targetEntity: Asset::class)]
    #[JoinColumn(name: 'asset_id', referencedColumnName: 'id')]
    #[Groups(['position:item:read'])]
    protected ?Asset $asset = null;

    #[ApiProperty(readableLink: true)]
    #[OneToOne(targetEntity: PositionValue::class)]
    #[JoinColumn(name: 'last_value_id', referencedColumnName: 'id')]
    #[Groups(['position:item:read', 'position:collection:read'])]
    #[Context(context: ['groups' => 'positionValue:cascade:read'])]
    protected ?PositionValue $lastValue = null;

    #[ApiProperty(readableLink: true)]
    #[ManyToMany(targetEntity: Tag::class)]
    #[JoinTable(name: 'position_tag')]
    #[JoinColumn(name: 'position_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Groups(['position:item:read', 'position:collection:read'])]
    #[Context(context: ['groups' => 'tag:cascade:read'])]
    protected Collection $tags;

    #[ApiProperty(readableLink: true)]
    #[ManyToOne(targetEntity: Account::class)]
    #[JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['position:item:read', 'position:collection:read'])]
    #[Context(context: ['groups' => 'account:cascade:read'])]
    protected ?Account $account = null;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->distributions = Money::USD(0);
        $this->contributions = Money::USD(0);
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    // Add name here so other entities can redefine it
    #[Groups(['position:item:read', 'position:collection:read', 'position:cascade:read'])]
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function isStarted(): bool
    {
        return !is_null($this->getStartDate());
    }

    public function getCompleteDate(): ?DateTime
    {
        return $this->completeDate;
    }

    public function setCompleteDate(?DateTime $completeDate): void
    {
        $this->completeDate = $completeDate;
    }

    public function isCompleted(): bool
    {
        return !is_null($this->getCompleteDate());
    }

    public function setAsset(Asset $asset): void
    {
        $this->asset = $asset;
    }

    public function getLastValue(): ?PositionValue
    {
        return $this->lastValue;
    }

    public function setLastValue(?PositionValue $lastValue): void
    {
        if (is_null($lastValue)) {
            $this->removeLastValue();

            return;
        }
        if (is_null($this->startDate) && !is_null($lastValue->getPositionEvent())) {
            $this->startDate = $lastValue->getPositionEvent()->getDate();
        }
        $this->lastValue = $lastValue;
    }

    public function removeLastValue(): void
    {
        $this->lastValue = null;
    }

    public function getDistributions(): Money
    {
        return $this->distributions;
    }

    public function setDistributions(Money $distributions): void
    {
        $this->distributions = $distributions;
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    #[SerializedName('distributions')]
    public function getDistributionsStruct(): ?array
    {
        return $this->formatMoney($this->distributions);
    }

    public function getContributions(): Money
    {
        return $this->contributions;
    }

    public function setContributions(Money $contributions): void
    {
        $this->contributions = $contributions;
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    #[SerializedName('contributions')]
    public function getContributionsStruct(): ?array
    {
        return $this->formatMoney($this->contributions);
    }

    public function addTag(Tag $tag): void
    {
        Assert::eq($tag->getCreatedBy()->getId(), $this->getCreatedBy()->getId());
        $this->tags->add($tag);
    }

    public function addTags(array $tags): void
    {
        foreach ($tags as $tag) {
            $this->tags->add($tag);
        }
    }

    public function setTags(array $tags): void
    {
        $this->tags->clear();
        $this->addTags($tags);
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): void
    {
        $this->account = $account;
    }

    public function getIrr(): string
    {
        return $this->irr;
    }

    public function setIrr(string $irr): void
    {
        $this->irr = $irr;
    }

    public function setMultiplier(string $multiplier): void
    {
        $this->multiplier = $multiplier;
    }

    public function getMultiplier(): string
    {
        return $this->multiplier;
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getStatus(): string
    {
        if (is_null($this->startDate)) {
            return 'Not Started';
        }
        if (is_null($this->getCompleteDate())) {
            return 'In Progress';
        }

        return 'Completed';
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    public function isFullyCommitted(): bool
    {
        return true;
    }

    public function getStatsData(DateTime $from, DateTime $to): PositionStatsData
    {
        return new PositionStatsData(count: 1, active: !is_null($this->startDate) && is_null($this->getCompleteDate()) ? 1 : 0, notStarted: is_null($this->startDate) ? 1 : 0, completed: !is_null($this->getCompleteDate()) ? 1 : 0, new: !is_null($this->startDate) && $this->startDate >= $from ? 1 : 0);
    }
}
