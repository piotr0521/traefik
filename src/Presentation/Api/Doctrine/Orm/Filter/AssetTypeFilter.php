<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Doctrine\Orm\Filter;

use ApiPlatform\Doctrine\Common\PropertyHelperTrait;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

class AssetTypeFilter extends AbstractFilter
{
    use PropertyHelperTrait;

    public function __construct(
        protected RepositoryInterface $assetTypeRepository,
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger = null,
        ?array $properties = null,
        ?NameConverterInterface $nameConverter = null
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ('assetType' !== $property) {
            return;
        }

        // Generate a unique parameter name to avoid collisions with other filters
        $parameterName = $queryNameGenerator->generateParameterName($property);

        $property = $this->properties['path'];
        // load all children and emulate API platform request
        $values = array_merge([$value], array_map(fn ($el) => strval($el->getId()), $this->assetTypeRepository->findBy(['parent' => $value])));

        [$alias, $field] = $this->addJoinsForNestedProperty($property, $queryBuilder->getRootAliases()[0], $queryBuilder, $queryNameGenerator, $resourceClass, Join::INNER_JOIN);

        $queryBuilder
            ->andWhere($queryBuilder->expr()->in(sprintf('%s.%s', $alias, $field), ':'.$parameterName))
            ->setParameter($parameterName, $values);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'assetType' => [
                'property' => null,
                'type' => 'string',
                'required' => false,
                'openapi' => [
                    'description' => 'Search by asset type',
                ],
            ],
        ];
    }
}
