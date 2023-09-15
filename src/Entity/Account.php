<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Domain\Enum\AccountSync;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Talav\Component\Resource\Model\Creatable;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\User\Model\CreatedBy;
use Talav\Component\User\Model\UserInterface;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['account:item:read'], 'swagger_definition_name' => 'Item Read']),
        new GetCollection(normalizationContext: ['groups' => ['account:collection:read'], 'swagger_definition_name' => 'Collection Read']),
    ],
    order: ['name' => 'ASC'],
    paginationEnabled: false
)]
#[Entity]
class Account implements ResourceInterface
{
    use ResourceTrait;
    use Creatable;
    use CreatedBy;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['account:item:read', 'account:collection:read'])]
    protected mixed $id;

    #[Column(type: 'string', length: 250, nullable: true)]
    #[Groups(['account:item:read', 'account:collection:read', 'account:cascade:read'])]
    protected ?string $name = null;

    #[Column(type: 'string', length: 250, nullable: true)]
    #[Groups(['account:item:read', 'account:collection:read', 'account:cascade:read'])]
    protected ?string $officialName = null;

    #[Column(type: 'string', length: 10, nullable: true)]
    protected ?string $mask = null;

    #[Column(type: 'string', enumType: AccountSync::class)]
    protected AccountSync $accountSync;

    #[Column(name: 'created_at', type: 'datetime', nullable: false)]
    #[GedmoTimestampable(on: 'create')]
    protected ?DateTime $createdAt = null;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    #[Groups(['account:item:read', 'account:collection:read', 'account:cascade:read'])]
    protected ?UserInterface $createdBy = null;

    #[ManyToOne(targetEntity: AccountHolder::class)]
    #[JoinColumn(name: 'account_holder_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['account:item:read', 'account:collection:read', 'account:cascade:read'])]
    protected ?AccountHolder $accountHolder = null;

    #[ManyToOne(targetEntity: PlaidConnection::class)]
    #[JoinColumn(name: 'plaid_connection_id', referencedColumnName: 'id', nullable: true)]
    protected ?PlaidConnection $plaidConnection = null;

    #[Column(type: 'string', nullable: true)]
    protected ?string $plaidId = null;

    #[ManyToOne(targetEntity: Institution::class)]
    #[JoinColumn(name: 'institution_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['account:item:read', 'account:collection:read', 'account:cascade:read'])]
    protected ?Institution $institution = null;

    #[ApiProperty(readableLink: true)]
    #[ManyToOne(targetEntity: AccountType::class)]
    #[JoinColumn(name: 'account_type_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['account:item:read', 'account:collection:read', 'account:cascade:read'])]
    #[Context(context: ['groups' => 'accountType:cascade:read'])]
    protected ?AccountType $accountType = null;

    public function getId(): mixed
    {
        return $this->id;
    }

    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getOfficialName(): ?string
    {
        return $this->officialName;
    }

    public function setOfficialName(?string $officialName): void
    {
        $this->officialName = $officialName;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getAccountHolder(): ?AccountHolder
    {
        return $this->accountHolder;
    }

    public function setAccountHolder(?AccountHolder $accountHolder): void
    {
        $this->accountHolder = $accountHolder;
    }

    public function getMask(): ?string
    {
        return $this->mask;
    }

    public function setMask(?string $mask): void
    {
        $this->mask = $mask;
    }

    public function getPlaidId(): ?string
    {
        return $this->plaidId;
    }

    public function setPlaidId(?string $plaidId): void
    {
        $this->plaidId = $plaidId;
    }

    public function getAccountSync(): AccountSync
    {
        return $this->accountSync;
    }

    public function setAccountSync(AccountSync $accountSync): void
    {
        $this->accountSync = $accountSync;
    }

    public function getPlaidConnection(): ?PlaidConnection
    {
        return $this->plaidConnection;
    }

    public function setPlaidConnection(?PlaidConnection $plaidConnection): void
    {
        $this->plaidConnection = $plaidConnection;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): void
    {
        $this->institution = $institution;
    }

    public function getAccountType(): ?AccountType
    {
        return $this->accountType;
    }

    public function setAccountType(?AccountType $accountType): void
    {
        $this->accountType = $accountType;
    }

    public function isDepository(): bool
    {
        $type = $this->getAccountType();
        if (is_null($type->getParent())) {
            return false;
        }

        return 'depository' === $type->getParent()->getPlaidName();
    }
}
