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
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Domain\Enum\Color;
use Groshy\Message\Command\Tag\CreateTagCommand;
use Groshy\Message\Command\Tag\DeleteTagCommand;
use Groshy\Message\Command\Tag\UpdateTagCommand;
use Groshy\Message\Dto\Tag\CreateTagDto;
use Groshy\Message\Dto\Tag\UpdateTagDto;
use Groshy\Presentation\Api\Dto\Tag\ApiCreateTagDto;
use Groshy\Presentation\Api\Dto\Tag\ApiUpdateTagDto;
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
        new Get(normalizationContext: ['groups' => ['tag:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(input: ['class' => ApiUpdateTagDto::class, 'transform' => ['dto' => UpdateTagDto::class, 'command' => UpdateTagCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(input: ['transform' => ['command' => DeleteTagCommand::class]], processor: ResourceStateProcessor::class),
        new Post(input: ['class' => ApiCreateTagDto::class, 'transform' => ['dto' => CreateTagDto::class, 'command' => CreateTagCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(normalizationContext: ['groups' => ['tag:collection:read'], 'swagger_definition_name' => 'Item Read']), ],
    order: ['position' => 'ASC']
)]
#[Entity]
#[Table(name: 'tag')]
#[UniqueConstraint(columns: ['name', 'created_by'])]
class Tag implements ResourceInterface
{
    use ResourceTrait;
    use Timestampable;
    use CreatedBy;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['tag:item:read', 'tag:collection:read', 'tag:cascade:read'])]
    protected mixed $id;

    #[Column(type: 'string', length: 250)]
    #[Groups(['tag:item:read', 'tag:collection:read', 'tag:cascade:read'])]
    protected ?string $name = null;

    #[Column(type: 'integer')]
    #[Groups(['tag:item:read', 'tag:collection:read', 'tag:cascade:read'])]
    protected int $position;

    #[Column(type: 'string', enumType: Color::class)]
    #[Groups(['tag:item:read', 'tag:collection:read', 'tag:cascade:read'])]
    protected Color $color;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    #[Groups(['tag:item:read'])]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    #[Groups(['tag:item:read'])]
    protected ?DateTime $updatedAt = null;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    #[Groups(['tag:item:read'])]
    protected ?UserInterface $createdBy = null;

    #[ApiProperty(readableLink: true)]
    #[ManyToOne(targetEntity: TagGroup::class, inversedBy: 'tags')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['tag:item:read', 'tag:collection:read'])]
    #[Context(context: ['groups' => ['tagGroup:cascade:read']])]
    protected ?TagGroup $tagGroup = null;

    public function __construct()
    {
        $this->position = 0;
        $this->color = Color::COLOR1;
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

    public function getColor(): Color
    {
        return $this->color;
    }

    public function setColor(Color $color): void
    {
        $this->color = $color;
    }

    public function getTagGroup(): ?TagGroup
    {
        return $this->tagGroup;
    }

    public function setTagGroup(TagGroup $tagGroup): void
    {
        $this->tagGroup = $tagGroup;
        $tagGroup->addTag($this);
    }
}
