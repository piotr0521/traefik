<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Metadata\ApiProperty;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Domain\Enum\Privacy;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\Resource\Model\Timestampable;
use Talav\Component\User\Model\CreatedBy;
use Talav\Component\User\Model\UserInterface;

#[Entity]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[DiscriminatorMap([
    'investment' => "Groshy\Entity\AssetInvestment",
    'cash' => "Groshy\Entity\AssetCash",
    'property' => "Groshy\Entity\AssetProperty",
    'certificate_deposit' => "Groshy\Entity\AssetCertificateDeposit",
    'collectable' => "Groshy\Entity\AssetCollectable",
    'security' => "Groshy\Entity\AssetSecurity",
    'crypto' => "Groshy\Entity\AssetCrypto",
    'business' => "Groshy\Entity\AssetBusiness",
    'credit_card' => "Groshy\Entity\LiabilityCreditCard",
    'mortgage' => "Groshy\Entity\LiabilityMortgage",
    'loan' => "Groshy\Entity\LiabilityLoan",
])]
#[Table(name: 'asset')]
class Asset implements ResourceInterface
{
    use ResourceTrait;
    use Timestampable;
    use CreatedBy;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['asset:item:read', 'asset:collection:read'])]
    protected mixed $id;

    #[Column(type: 'string', length: 250)]
    #[Groups(['asset:item:read', 'asset:collection:read'])]
    protected ?string $name = null;

    #[Column(type: 'string', length: 10, nullable: false, enumType: Privacy::class)]
    protected Privacy $privacy;

    #[Column(name: 'created_at', type: 'datetime', nullable: false)]
    #[GedmoTimestampable(on: 'create')]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime', nullable: false)]
    #[GedmoTimestampable(on: 'update')]
    protected ?DateTime $updatedAt = null;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    protected ?UserInterface $createdBy = null;

    #[ManyToOne(targetEntity: AssetType::class)]
    #[JoinColumn(name: 'asset_type_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['asset:item:read'])]
    #[Context(context: ['groups' => 'assetType:cascade:read'])]
    #[ApiProperty(readableLink: true)]
    protected ?AssetType $assetType = null;

    #[ManyToOne(targetEntity: Sponsor::class, cascade: ['persist'])]
    #[JoinColumn(name: 'sponsor_id', referencedColumnName: 'id')]
    protected ?Sponsor $sponsor = null;

    protected ?AssetConfig $config = null;

    public const ASSET_CASH = 'Cash';
    public const ASSET_CERTIFICATE_DEPOSIT = 'Certificate of Deposit';
    public const ASSET_COLLECTABLE = 'Collectables';
    public const LIABILITY_CREDIT_CARD = 'Credit Card';
    public const LIABILITY_MORTGAGE = 'Mortgage';
    public const LIABILITY_LOAN = 'Loan';

    public function __construct()
    {
        $this->config = $this->createConfig();
        $this->privacy = $this->config->getDefaultPrivacy();
        $this->id = Uuid::uuid4();
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPrivacy(): Privacy
    {
        return $this->privacy;
    }

    public function setPrivacy(Privacy $privacy): void
    {
        if (!$this->getConfig()->isAllowPrivacyChange()) {
            throw new \RuntimeException('Privacy Policy change is not allowed for this asset');
        }
        $this->privacy = $privacy;
    }

    public function getAssetType(): ?AssetType
    {
        return $this->assetType;
    }

    public function setAssetType(AssetType $assetType): void
    {
        $this->assetType = $assetType;
    }

    public function getRootAssetType(): ?AssetType
    {
        if (is_null($this->assetType)) {
            return null;
        }
        $parent = $this->assetType->getParent();

        return is_null($parent) ? $this->assetType : $parent;
    }

    public function getSponsor(): ?Sponsor
    {
        return $this->sponsor;
    }

    public function setSponsor(?Sponsor $sponsor): void
    {
        $this->sponsor = $sponsor;
    }

    public function createConfig(): AssetConfig
    {
        return new AssetConfig(Privacy::PUBLIC, true);
    }

    protected function getConfig(): AssetConfig
    {
        if (is_null($this->config)) {
            $this->config = $this->createConfig();
        }

        return $this->config;
    }
}
