<?php

declare(strict_types=1);

namespace Groshy\Mapper\Dto;

use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\CustomMapper\CustomMapper;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\CreateTransactionDto;
use Groshy\Message\Dto\PositionEvent\PositionValueDto;
use Groshy\Presentation\Api\Dto\PositionEvent\ApiCreatePositionEventDto;
use Webmozart\Assert\Assert;

class PositionEventCreateDtoMapper extends CustomMapper
{
    public function __construct(
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function mapToObject($source, $destination)
    {
        /* @var ApiCreatePositionEventDto $source */
        Assert::isInstanceOf($source, ApiCreatePositionEventDto::class);
        /* @var CreatePositionEventDto $destination */
        Assert::isInstanceOf($destination, CreatePositionEventDto::class);

        $destination->type = PositionEventType::from($source->type);
        $destination->notes = $source->notes;
        $destination->position = $source->position;
        $destination->date = $source->date;
        $destination->value = $this->mapper->map($source->value, PositionValueDto::class);
        $destination->transactions = $this->mapper->mapMultiple($source->transactions, CreateTransactionDto::class);

        return $destination;
    }
}
