<?php

declare(strict_types=1);

namespace Groshy\Message\Command\Sponsor;

use Groshy\Message\Dto\Sponsor\CreateSponsorDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreateSponsorCommand implements DomainEventInterface
{
    public function __construct(
        public CreateSponsorDto $dto
    ) {
    }
}
