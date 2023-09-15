<?php

declare(strict_types=1);

namespace Groshy\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Talav\Component\Resource\Model\Creatable;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\User\Model\CreatedBy;
use Talav\Component\User\Model\UserInterface;

// Connection between institutions and users
#[Entity]
class PlaidConnection implements ResourceInterface
{
    use ResourceTrait;
    use Creatable;
    use CreatedBy;

    #[Id]
    #[Column(type: 'string', unique: true)]
    protected mixed $id;

    #[Column(type: 'string', length: 250)]
    protected ?string $accessToken = null;

    #[Column(name: 'created_at', type: 'datetime', nullable: false)]
    #[GedmoTimestampable(on: 'create')]
    protected ?DateTime $createdAt = null;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    protected ?UserInterface $createdBy = null;

    #[ManyToOne(targetEntity: Institution::class)]
    #[JoinColumn(name: 'institution_id', referencedColumnName: 'id', nullable: false)]
    protected ?Institution $institution = null;

    public function getId(): mixed
    {
        return $this->id;
    }

    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedBy(): ?UserInterface
    {
        return $this->createdBy;
    }

    public function setCreatedBy(UserInterface $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(Institution $institution): void
    {
        $this->institution = $institution;
    }
}
