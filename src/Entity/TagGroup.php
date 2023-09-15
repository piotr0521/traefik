<?php

declare(strict_types=1);

namespace Groshy\Entity;

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
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Message\Command\TagGroup\CreateTagGroupCommand;
use Groshy\Message\Command\TagGroup\DeleteTagGroupCommand;
use Groshy\Message\Command\TagGroup\UpdateTagGroupCommand;
use Groshy\Message\Dto\TagGroup\CreateTagGroupDto;
use Groshy\Message\Dto\TagGroup\UpdateTagGroupDto;
use Groshy\Presentation\Api\Dto\TagGroup\ApiCreateTagGroupDto;
use Groshy\Presentation\Api\Dto\TagGroup\ApiUpdateTagGroupDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\Resource\Model\Timestampable;
use Talav\Component\User\Model\CreatedBy;
use Talav\Component\User\Model\UserInterface;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['tagGroup:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(input: ['class' => ApiUpdateTagGroupDto::class, 'transform' => ['dto' => UpdateTagGroupDto::class, 'command' => UpdateTagGroupCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(input: ['transform' => ['command' => DeleteTagGroupCommand::class]], processor: ResourceStateProcessor::class),
        new Post(input: ['class' => ApiCreateTagGroupDto::class, 'transform' => ['dto' => CreateTagGroupDto::class, 'command' => CreateTagGroupCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(normalizationContext: ['groups' => ['tagGroup:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    order: ['position' => 'ASC']
)]
#[Entity]
#[Table(name: 'tag_group')]
#[UniqueConstraint(columns: ['name', 'created_by'])]
class TagGroup implements ResourceInterface
{
    use ResourceTrait;
    use Timestampable;
    use CreatedBy;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['tagGroup:item:read', 'tagGroup:collection:read'])]
    protected mixed $id;

    #[Column(type: 'string', length: 250)]
    #[Groups(['tagGroup:item:read', 'tagGroup:collection:read', 'tagGroup:cascade:read'])]
    protected ?string $name = null;

    #[Column(type: 'integer')]
    #[Groups(['tagGroup:item:read', 'tagGroup:collection:read'])]
    protected int $position;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    #[Groups(['tagGroup:item:read'])]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    #[Groups(['tagGroup:item:read'])]
    protected ?DateTime $updatedAt = null;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    #[Groups(['tagGroup:item:read'])]
    protected ?UserInterface $createdBy = null;

    #[ApiProperty(readableLink: true)]
    #[OneToMany(mappedBy: 'tagGroup', targetEntity: Tag::class, cascade: ['remove'], indexBy: 'position')]
    #[Groups(['tagGroup:item:read', 'tagGroup:collection:read'])]
    #[Context(context: ['groups' => ['tag:cascade:read']])]
    #[OrderBy(['position' => 'ASC'])]
    protected Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->position = 0;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setTagGroup($this);
        }
    }
}
