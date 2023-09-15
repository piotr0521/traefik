<?php

declare(strict_types=1);

namespace Groshy\Message\Command\Position;

use Groshy\Entity\Position;
use Talav\Component\Resource\Model\DomainEventInterface;
use Webmozart\Assert\Assert;

final class CalculatePositionListCommand implements DomainEventInterface
{
    /**
     * @param Position[] $positions
     */
    public function __construct(
        public array $positions
    ) {
        Assert::allIsInstanceOf($positions, Position::class);
    }
}
