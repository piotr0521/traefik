<?php

declare(strict_types=1);

namespace Groshy\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;

#[Entity]
class AssetTypeAccountType implements ResourceInterface
{
    use ResourceTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    protected mixed $id;

    #[ManyToOne(targetEntity: AccountType::class)]
    #[JoinColumn(name: 'account_type_id', referencedColumnName: 'id', nullable: false)]
    protected ?AccountType $accountType = null;

    #[ManyToOne(targetEntity: AssetType::class)]
    #[JoinColumn(name: 'asset_type_id', referencedColumnName: 'id', nullable: false)]
    protected ?AssetType $assetType = null;

    #[Column(name: 'is_subtype', type: 'boolean')]
    protected ?bool $isSubtype = null;

    public function getId(): mixed
    {
        return $this->id;
    }

    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function getAccountType(): ?AccountType
    {
        return $this->accountType;
    }

    public function setAccountType(?AccountType $accountType): void
    {
        $this->accountType = $accountType;
    }

    public function getAssetType(): ?AssetType
    {
        return $this->assetType;
    }

    public function setAssetType(?AssetType $assetType): void
    {
        $this->assetType = $assetType;
    }

    public function getIsSubtype(): ?bool
    {
        return $this->isSubtype;
    }

    public function setIsSubtype(?bool $isSubtype): void
    {
        $this->isSubtype = $isSubtype;
    }
}
