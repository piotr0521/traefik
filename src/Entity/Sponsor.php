<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Domain\Enum\Privacy;
use Groshy\Message\Command\Sponsor\CreateSponsorCommand;
use Groshy\Message\Command\Sponsor\DeleteSponsorCommand;
use Groshy\Message\Command\Sponsor\UpdateSponsorCommand;
use Groshy\Message\Dto\Sponsor\CreateSponsorDto;
use Groshy\Message\Dto\Sponsor\UpdateSponsorDto;
use Groshy\Presentation\Api\Dto\Sponsor\ApiCreateSponsorDto;
use Groshy\Presentation\Api\Dto\Sponsor\ApiSponsorStatsDto;
use Groshy\Presentation\Api\Dto\Sponsor\ApiUpdateSponsorDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Groshy\Presentation\Api\State\Sponsor\SponsorStatsProvider;
use Groshy\Validator\Constraints\DeletableSponsor;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\Resource\Model\Timestampable;
use Talav\Component\User\Model\CreatedBy;
use Talav\Component\User\Model\UserInterface;

#[DeletableSponsor(groups: ['delete'])]
#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'], normalizationContext: ['groups' => ['sponsor:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Post(input: ['class' => ApiCreateSponsorDto::class, 'transform' => ['dto' => CreateSponsorDto::class, 'command' => CreateSponsorCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(normalizationContext: ['groups' => ['sponsor:collection:read'], 'swagger_definition_name' => 'Collection Read']),
        new GetCollection(uriTemplate: '/sponsors/stats.{_format}', filters: [], output: ApiSponsorStatsDto::class, provider: SponsorStatsProvider::class),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or object.getCreatedBy() == user",
            input: ['transform' => ['command' => DeleteSponsorCommand::class]],
            processor: ResourceStateProcessor::class
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN') or object.getCreatedBy() == user",
            input: ['class' => ApiUpdateSponsorDto::class, 'transform' => ['dto' => UpdateSponsorDto::class, 'command' => UpdateSponsorCommand::class]],
            processor: ResourceStateProcessor::class
        ),
    ],
    order: ['name' => 'ASC']
)]
#[Entity]
#[Table(name: 'sponsor')]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['name' => 'partial', 'privacy' => 'exact'])]
class Sponsor implements ResourceInterface
{
    use ResourceTrait;
    use Timestampable;
    use CreatedBy;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['sponsor:item:read', 'sponsor:collection:read'])]
    protected mixed $id;

    #[Column(type: 'string', length: 250)]
    #[Groups(['sponsor:item:read', 'sponsor:collection:read', 'sponsor:cascade:read'])]
    protected ?string $name = null;

    #[Column(type: 'string', length: 250, nullable: true)]
    #[Groups(['sponsor:item:read', 'sponsor:collection:read'])]
    protected ?string $website = null;

    #[Column(type: 'string', length: 10, enumType: Privacy::class)]
    #[Groups(['sponsor:item:read', 'sponsor:collection:read'])]
    protected Privacy $privacy;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    #[Groups(['sponsor:item:read'])]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    #[Groups(['sponsor:item:read'])]
    protected ?DateTime $updatedAt = null;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    protected ?UserInterface $createdBy = null;

    #[OneToOne(targetEntity: Institution::class)]
    #[JoinColumn(name: 'institution_id', referencedColumnName: 'id', nullable: true)]
    protected ?Institution $institution = null;

    public function __construct()
    {
        $this->privacy = Privacy::PUBLIC;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getPrivacy(): Privacy
    {
        return $this->privacy;
    }

    public function isPublic(): bool
    {
        return Privacy::PUBLIC == $this->getPrivacy();
    }

    public function setPrivacy(Privacy $privacy): void
    {
        $this->privacy = $privacy;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): void
    {
        $this->institution = $institution;
    }
}
