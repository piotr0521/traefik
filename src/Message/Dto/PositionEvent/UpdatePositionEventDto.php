<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionEvent;

use DateTime;
use Groshy\Domain\Enum\PositionEventType;

class UpdatePositionEventDto
{
    public ?DateTime $date = null;

    public ?PositionValueDto $value = null;

    public ?PositionEventType $type = null;

    public ?string $notes = null;

    /** @var array<UpdateTransactionDto> */
    public array $transactions = [];
}
