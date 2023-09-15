<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\Sponsor;

use ApiPlatform\Metadata\ApiProperty;
use Groshy\Entity\Sponsor;
use Symfony\Component\Serializer\Annotation\Context;

class ApiSponsorStatsDto
{
    public function __construct(
        #[Context(context: ['groups' => 'sponsor:cascade:read'])]
        #[ApiProperty(readableLink: true)]
        public Sponsor $sponsor,
        public int $total
    ) {
    }
}
