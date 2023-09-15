<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\State\Year;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Groshy\Presentation\Api\Dto\Year\Year;
use Symfony\Component\Security\Core\Security;
use Talav\Component\Resource\Repository\RepositoryInterface;

class YearStatsProvider implements ProviderInterface
{
    public function __construct(
        private readonly RepositoryInterface $positionRepository,
        private readonly Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $return = [];
        $result = $this->positionRepository->groupByYear($this->security->getUser());
        foreach ($result as $el) {
            $return[] = new Year(intval($el['year']), intval($el['total']));
        }

        return $return;
    }
}
