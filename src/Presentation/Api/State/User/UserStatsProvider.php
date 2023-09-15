<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DateTime;
use Groshy\Provider\DashboardProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class UserStatsProvider implements ProviderInterface
{
    public function __construct(
        private readonly DashboardProvider $provider,
        private readonly RepositoryInterface $positionRepository,
        private readonly RepositoryInterface $assetTypeRepository,
        private readonly RepositoryInterface $userRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->userRepository->find($uriVariables['id']);
        $from = DateTime::createFromFormat('Y-m-d', $this->requestStack->getCurrentRequest()->query->get('from'));
        $to = DateTime::createFromFormat('Y-m-d', $this->requestStack->getCurrentRequest()->query->get('to'));
        $typeId = $this->requestStack->getCurrentRequest()->query->get('type');
        $positionId = $this->requestStack->getCurrentRequest()->query->get('position');
        $type = is_null($typeId) ? null : $this->assetTypeRepository->find($typeId);
        $position = is_null($positionId) ? null : $this->positionRepository->findOneBy(['id' => $positionId]);

        return new JsonResponse($this->provider->getDashboardData($from, $to, $user, $type, $position), 200);
    }
}
