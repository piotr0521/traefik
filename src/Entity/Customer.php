<?php

declare(strict_types=1);

namespace Groshy\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Timestampable as GedmoTimestampable;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Talav\Component\Resource\Model\ResourceInterface;
use Talav\Component\Resource\Model\ResourceTrait;
use Talav\Component\Resource\Model\Timestampable;

#[Entity]
#[Table(name: 'billing_customer')]
class Customer implements ResourceInterface
{
    use ResourceTrait;
    use Timestampable;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    #[GeneratedValue(strategy: 'CUSTOM')]
    #[CustomIdGenerator(class: UuidGenerator::class)]
    protected mixed $id;

    #[Column(name: 'is_delinquent', type: 'boolean')]
    protected bool $isDelinquent = false;

    #[Column(type: 'string', length: 250)]
    protected ?string $stripeId = null;

    #[Column(name: 'created_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'create')]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at', type: 'datetime')]
    #[GedmoTimestampable(on: 'update')]
    protected ?DateTime $updatedAt = null;

    #[OneToOne(inversedBy: 'customer', targetEntity: User::class)]
    protected ?User $user = null;

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(string $stripeId): void
    {
        $this->stripeId = $stripeId;
    }

    public function isDelinquent(): bool
    {
        return $this->isDelinquent;
    }

    public function setIsDelinquent(bool $isDelinquent): void
    {
        $this->isDelinquent = $isDelinquent;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
        if ($user->getCustomer() !== $this) {
            $user->setCustomer($this);
        }
    }
}
