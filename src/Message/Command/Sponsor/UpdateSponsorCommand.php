<?php

declare(strict_types=1);

namespace Groshy\Message\Command\Sponsor;

use Groshy\Entity\Sponsor;
use Groshy\Message\Dto\Sponsor\UpdateSponsorDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdateSponsorCommand implements DomainEventInterface
{
    public function __construct(
        public Sponsor $sponsor,
        public UpdateSponsorDto $dto
    ) {
    }
}
