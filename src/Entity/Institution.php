<?php

namespace Groshy\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Message\Command\CreateResourceCommand;
use Groshy\Message\Dto\Institution\CreateInstitutionDto;
use Groshy\Presentation\Api\Dto\Institution\ApiCreateInstitutionDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\Resource\Model\Timestampable;
use Talav\Component\User\Model\CreatedBy;
use Talav\Component\User\Model\UserInterface;

#[ApiResource(
    operations: [
        new Get(),
        new Post(input: ['class' => ApiCreateInstitutionDto::class, 'transform' => ['dto' => CreateInstitutionDto::class, 'command' => CreateResourceCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['ins:read'], 'swagger_definition_name' => 'Read'],
    order: ['name' => 'ASC']
)]
#[Entity]
#[Table(name: 'institution')]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['name' => 'partial'])]
class Institution implements ResourceInterface
{
    use ResourceTrait;
    use Timestampable;
    use CreatedBy;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['ins:read'])]
    protected mixed $id;

    #[Column(type: 'string', length: 250)]
    #[Groups(['ins:read'])]
    protected ?string $name = null;

    #[Column(type: 'string', length: 512)]
    #[Groups(['ins:read'])]
    protected ?string $website = null;

    #[Column(type: 'string', length: 250, unique: true, nullable: true)]
    protected ?string $plaidId = null;

    #[Column(type: 'string', length: 250)]
    #[Gedmo\Slug(fields: ['name'])]
    protected ?string $slug = null;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    #[Groups(['ins:read'])]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    #[Groups(['ins:read'])]
    protected ?DateTime $updatedAt = null;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    protected ?UserInterface $createdBy = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getPlaidId(): ?string
    {
        return $this->plaidId;
    }

    public function setPlaidId(?string $plaidId): void
    {
        $this->plaidId = $plaidId;
    }
}
