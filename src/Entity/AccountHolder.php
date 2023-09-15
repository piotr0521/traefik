<?php

declare(strict_types=1);

namespace Groshy\Entity;

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
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Groshy\Message\Command\AccountHolder\CreateAccountHolderCommand;
use Groshy\Message\Command\AccountHolder\DeleteAccountHolderCommand;
use Groshy\Message\Command\AccountHolder\UpdateAccountHolderCommand;
use Groshy\Message\Dto\AccountHolder\CreateAccountHolderDto;
use Groshy\Message\Dto\AccountHolder\UpdateAccountHolderDto;
use Groshy\Presentation\Api\Dto\AccountHolder\ApiCreateAccountHolderDto;
use Groshy\Presentation\Api\Dto\AccountHolder\ApiUpdateAccountHolderDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Talav\Component\Resource\Model\Creatable;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\User\Model\CreatedBy;
use Talav\Component\User\Model\UserInterface;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['accountHolder:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Post(input: ['class' => ApiCreateAccountHolderDto::class, 'transform' => ['dto' => CreateAccountHolderDto::class, 'command' => CreateAccountHolderCommand::class]], processor: ResourceStateProcessor::class),
        new Patch(input: ['class' => ApiUpdateAccountHolderDto::class, 'transform' => ['dto' => UpdateAccountHolderDto::class, 'command' => UpdateAccountHolderCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(input: ['transform' => ['command' => DeleteAccountHolderCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(normalizationContext: ['groups' => ['accountHolder:collection:read'], 'swagger_definition_name' => 'Collection Read']),
    ],
    order: ['name' => 'ASC'],
    paginationEnabled: false
)]
#[Entity]
class AccountHolder implements ResourceInterface
{
    use ResourceTrait;
    use Creatable;
    use CreatedBy;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['accountHolder:item:read', 'accountHolder:collection:read'])]
    protected mixed $id;

    #[Column(type: 'string', length: 250)]
    #[Groups(['accountHolder:item:read', 'accountHolder:collection:read', 'accountHolder:cascade:read'])]
    protected ?string $name = null;

    #[Column(name: 'created_at', type: 'datetime', nullable: false)]
    #[GedmoTimestampable(on: 'create')]
    protected ?DateTime $createdAt = null;

    #[ManyToOne(targetEntity: UserInterface::class)]
    #[JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    #[Groups(['accountHolder:item:read', 'accountHolder:collection:read', 'accountHolder:cascade:read'])]
    protected ?UserInterface $createdBy = null;

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
}
