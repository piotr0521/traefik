<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Groshy\Domain\Calculation\ValueObject\ValueList;
use Groshy\Message\Command\User\UpdateUserCommand;
use Groshy\Message\Dto\User\UpdateUserDto;
use Groshy\Presentation\Api\Dto\Tag\ApiResetTagsDto;
use Groshy\Presentation\Api\Dto\User\ApiChangePasswordDto;
use Groshy\Presentation\Api\Dto\User\ApiUpdateUserDto;
use Groshy\Presentation\Api\Provider\UserGraphProvider;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Groshy\Presentation\Api\State\Tag\ResetTagsProcessor;
use Groshy\Presentation\Api\State\User\ChangePasswordProcessor;
use Groshy\Presentation\Api\State\User\UserStatsProvider;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Talav\UserBundle\Entity\User as BaseUser;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['user:item:read'], 'swagger_definition_name' => 'Item Read'],
            security: "is_granted('IS_AUTHENTICATED_FULLY') and id == user.getId()",
        ),
        new Get(
            uriTemplate: '/users/{id}/stats',
            openapiContext: [
                'summary' => 'Returns a dashboard for the provided user',
                'parameters' => [
                    ['name' => 'from', 'type' => DateTime::class, 'in' => 'query', 'description' => 'Start date for user stats'],
                    ['name' => 'to', 'type' => DateTime::class, 'in' => 'query', 'description' => 'End date for user stats'],
                    ['name' => 'type', 'type' => 'integer', 'in' => 'query', 'description' => 'Asset type UUID'],
                    ['name' => 'position', 'type' => 'string', 'in' => 'query', 'description' => 'Position UUID'],
                ],
            ],
            security: "is_granted('IS_AUTHENTICATED_FULLY') and id == user.getId()",
            provider: UserStatsProvider::class
        ),
        new Get(
            uriTemplate: '/users/{id}/graph',
            openapiContext: [
                'summary' => 'Returns a graph for the provided user',
                'parameters' => [
                    ['name' => 'from', 'type' => DateTime::class, 'in' => 'query', 'description' => 'Start date for user stats'],
                    ['name' => 'to', 'type' => DateTime::class, 'in' => 'query', 'description' => 'End date for user stats'],
                    ['name' => 'type', 'type' => 'integer', 'in' => 'query', 'description' => 'Asset type UUID'],
                    ['name' => 'position', 'type' => 'string', 'in' => 'query', 'description' => 'Position UUID'],
                ],
            ],
            security: "is_granted('IS_AUTHENTICATED_FULLY') and id == user.getId()",
            output: ValueList::class,
            provider: UserGraphProvider::class
        ),
        new Patch(
            security: "is_granted('IS_AUTHENTICATED_FULLY') and id == user.getId()",
            input: ['class' => ApiUpdateUserDto::class, 'transform' => ['dto' => UpdateUserDto::class, 'command' => UpdateUserCommand::class]],
            processor: ResourceStateProcessor::class,
        ),
        new Put(
            uriTemplate: '/users/{id}/password',
            security: "is_granted('IS_AUTHENTICATED_FULLY') and id == user.getId()",
            input: ['class' => ApiChangePasswordDto::class],
            processor: ChangePasswordProcessor::class,
        ),
        new Put(
            uriTemplate: '/users/{id}/reset_tags',
            status: 202,
            openapiContext: [
                'summary' => 'Resets tags to their default values',
                'description' => 'Resets tags to their default values',
            ],
            security: "is_granted('IS_AUTHENTICATED_FULLY') and id == user.getId()",
            input: ApiResetTagsDto::class,
            output: false,
            validate: false,
            processor: ResetTagsProcessor::class,
        ),
    ]
)]
#[Entity]
#[Table(name: 'user')]
class User extends BaseUser
{
    public const ROLE_CUSTOMER = 'ROLE_CUSTOMER';

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(['user:item:read'])]
    protected mixed $id = null;

    #[Groups(['user:item:read'])]
    protected ?string $firstName = null;

    #[Groups(['user:item:read'])]
    protected ?string $lastName = null;

    #[Groups(['user:item:read'])]
    protected ?string $username = null;

    #[OneToOne(mappedBy: 'user', targetEntity: Customer::class)]
    protected ?Customer $customer = null;

    public function getFullName(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
        if ($customer->getUser() !== $this) {
            $customer->setUser($this);
        }
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }
}
