<?php

declare(strict_types=1);

namespace Groshy\Message\Command\PositionEvent;

use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Talav\Component\Resource\Model\DomainEventInterface;
use Webmozart\Assert\Assert;

final class CreatePositionEventListCommand implements DomainEventInterface
{
    /**
     * @param CreatePositionEventDto[] $dtoList
     */
    public function __construct(
        public array $dtoList
    ) {
        Assert::allIsInstanceOf($dtoList, CreatePositionEventDto::class);
    }
}
