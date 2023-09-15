<?php

namespace Groshy\Presentation\Api\Doctrine\Orm\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\Account;
use Groshy\Entity\AccountHolder;
use Groshy\Entity\AssetBusiness;
use Groshy\Entity\AssetInvestment;
use Groshy\Entity\AssetProperty;
use Groshy\Entity\Position;
use Groshy\Entity\PositionBusiness;
use Groshy\Entity\PositionCash;
use Groshy\Entity\PositionCertificateDeposit;
use Groshy\Entity\PositionCollectable;
use Groshy\Entity\PositionCreditCard;
use Groshy\Entity\PositionCrypto;
use Groshy\Entity\PositionEvent;
use Groshy\Entity\PositionInvestment;
use Groshy\Entity\PositionLoan;
use Groshy\Entity\PositionMortgage;
use Groshy\Entity\PositionProperty;
use Groshy\Entity\PositionSecurity;
use Groshy\Entity\PositionValue;
use Groshy\Entity\Sponsor;
use Groshy\Entity\Tag;
use Groshy\Entity\TagGroup;
use Groshy\Entity\Transaction;
use Symfony\Component\Security\Core\Security;

final class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private array $classesPrivacy = [
        Sponsor::class,
        AssetInvestment::class,
        AssetProperty::class,
        AssetBusiness::class,
    ];

    private array $classesOwner = [
        PositionInvestment::class,
        PositionCash::class,
        PositionCreditCard::class,
        PositionProperty::class,
        PositionCertificateDeposit::class,
        PositionCollectable::class,
        PositionMortgage::class,
        PositionSecurity::class,
        PositionCrypto::class,
        PositionLoan::class,
        PositionBusiness::class,
        Position::class,
        Tag::class,
        TagGroup::class,
        Account::class,
        AccountHolder::class,
    ];

    private array $positionOwner = [
        Transaction::class,
        PositionValue::class,
        PositionEvent::class,
    ];

    public function __construct(private readonly Security $security)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (!in_array($resourceClass, array_merge($this->classesPrivacy, $this->classesOwner, $this->positionOwner)) || $this->security->isGranted('ROLE_ADMIN') || null === $user = $this->security->getUser()) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        if (in_array($resourceClass, $this->classesPrivacy)) {
            $queryBuilder->andWhere(sprintf('%s.createdBy = :user OR %s.privacy = :public', $rootAlias, $rootAlias))
                ->setParameter('public', Privacy::PUBLIC);
        } elseif (in_array($resourceClass, $this->classesOwner)) {
            $queryBuilder->andWhere(sprintf('%s.createdBy = :user', $rootAlias));
        } else {
            $queryBuilder->leftJoin(sprintf('%s.position', $rootAlias), 'position')
                ->andWhere('position.createdBy = :user');
        }
        $queryBuilder->setParameter('user', $user);
    }
}
