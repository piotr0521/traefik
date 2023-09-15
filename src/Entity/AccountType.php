<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\RelativeSlugHandler;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['accountType:item:read'], 'swagger_definition_name' => 'Item Read']),
        new GetCollection(normalizationContext: ['groups' => ['accountType:collection:read'], 'swagger_definition_name' => 'Collection Read']),
    ],
    order: ['name' => 'ASC'],
    paginationEnabled: false
)]
#[Entity]
class AccountType implements ResourceInterface
{
    use ResourceTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['accountType:item:read', 'accountType:collection:read'])]
    protected mixed $id;

    #[Column(type: 'string', length: 250)]
    #[Groups(['accountType:item:read', 'accountType:collection:read', 'accountType:cascade:read'])]
    protected ?string $name = null;

    #[Column(type: 'string', length: 250)]
    #[Groups(['accountType:item:read', 'accountType:collection:read'])]
    protected ?string $description = null;

    #[Column(type: 'string', length: 250)]
    protected ?string $plaidName = null;

    #[Column(type: 'string', length: 250, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    #[Gedmo\SlugHandler(class: RelativeSlugHandler::class, options: [
        'relationField' => 'parent',
        'relationSlugField' => 'slug',
        'separator' => '/',
    ])]
    #[Groups(['accountType:item:read'])]
    protected ?string $slug = null;

    #[OneToMany(mappedBy: 'parent', targetEntity: AccountType::class)]
    protected Collection $children;

    #[ManyToOne(targetEntity: AccountType::class, inversedBy: 'children')]
    #[Groups(['accountType:item:read', 'accountType:collection:read'])]
    protected ?AccountType $parent = null;

    public const TYPE_CD = 'CD';
    public const TYPE_CREDIT_CARD = 'Credit Card';
    public const TYPE_LOAN = 'General';
    public const TYPE_MORTGAGE = 'Mortgage';

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(ArrayCollection|Collection $children): void
    {
        $this->children = $children;
    }

    public function getParent(): ?AccountType
    {
        return $this->parent;
    }

    public function setParent(?AccountType $parent): void
    {
        $this->parent = $parent;
    }

    public function isTopLevel(): bool
    {
        return is_null($this->getParent());
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getPlaidName(): ?string
    {
        return $this->plaidName;
    }

    public function setPlaidName(?string $plaidName): void
    {
        $this->plaidName = $plaidName;
    }
}
