<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionEvent;

use DateTime;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\Position;

class CreatePositionEventDto
{
    public ?DateTime $date = null;

    public ?Position $position = null;

    public ?PositionValueDto $value = null;

    public ?PositionEventType $type = null;

    public ?string $notes = null;

    /** @var array<CreateTransactionDto> */
    public array $transactions = [];

    public static function factory(
        array $transactions = [],
        ?DateTime $date = null,
        ?Position $position = null,
        ?PositionValueDto $value = null,
        ?PositionEventType $type = null,
        ?string $notes = null,
    ): CreatePositionEventDto {
        $dto = new CreatePositionEventDto();
        $dto->transactions = $transactions;
        $dto->date = $date;
        $dto->position = $position;
        $dto->value = $value;
        $dto->type = $type;
        $dto->notes = $notes;

        return $dto;
    }
}
