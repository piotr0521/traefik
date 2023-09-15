<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DateTime;
use Groshy\Application\Query\UserGraphQuery;
use Groshy\Application\QueryHandler\UserGraphQueryHandler;
use Symfony\Component\HttpFoundation\RequestStack;
use Talav\Component\Resource\Repository\RepositoryInterface;

// Provider for user graph. This class connects presentation layer (API) and application layer
final class UserGraphProvider implements ProviderInterface
{
    public function __construct(
        private readonly RepositoryInterface $positionRepository,
        private readonly RepositoryInterface $assetTypeRepository,
        private readonly RepositoryInterface $userRepository,
        private readonly RequestStack $requestStack,
        private readonly UserGraphQueryHandler $queryHandler,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->userRepository->find($uriVariables['id']);
        $from = DateTime::createFromFormat('Y-m-d', $this->requestStack->getCurrentRequest()->query->get('from'));
        $to = DateTime::createFromFormat('Y-m-d', $this->requestStack->getCurrentRequest()->query->get('to'));
        $typeId = $this->requestStack->getCurrentRequest()->query->get('type');
        $positionId = $this->requestStack->getCurrentRequest()->query->get('position');
        if (!is_null($positionId)) {
            $positions = [$this->positionRepository->findOneBy(['id' => $positionId, 'createdBy' => $user])];
        } else {
            $positions = $this->positionRepository->getByInterval($from, $to, $user, !is_null($typeId) ? $this->assetTypeRepository->find($typeId) : null);
        }

        return $this->queryHandler->__invoke(new UserGraphQuery($from, $to, $positions));
    }
}
