<?php

declare(strict_types=1);

namespace Groshy\Message\Command\Sponsor;

use Groshy\Entity\Sponsor;
use Talav\Component\Resource\Model\DomainEventInterface;

final class DeleteSponsorCommand implements DomainEventInterface
{
    public function __construct(
        public Sponsor $sponsor
    ) {
    }
}
