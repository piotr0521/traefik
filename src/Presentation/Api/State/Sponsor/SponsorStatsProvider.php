<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\State\Sponsor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Groshy\Presentation\Api\Dto\Sponsor\ApiSponsorStatsDto;
use Symfony\Component\Security\Core\Security;
use Talav\Component\Resource\Repository\RepositoryInterface;

class SponsorStatsProvider implements ProviderInterface
{
    public function __construct(
        private readonly RepositoryInterface $positionRepository,
        private readonly RepositoryInterface $sponsorRepository,
        private readonly Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $result = $this->positionRepository->groupBySponsor($this->security->getUser());
        $sponsorIds = array_map(static fn (array $el) => $el['id'], $result);
        $sponsors = [];
        foreach ($this->sponsorRepository->findBy(['id' => $sponsorIds]) as $sp) {
            $sponsors[strval($sp->getId())] = $sp;
        }
        $return = [];
        foreach ($result as $el) {
            $return[] = new ApiSponsorStatsDto($sponsors[strval($el['id'])], intval($el['total']));
        }

        return $return;
    }
}
