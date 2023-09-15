<?php

declare(strict_types=1);

namespace Groshy\Mapper\Dto;

use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\CustomMapper\CustomMapper;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Message\Dto\PositionEvent\PositionValueDto;
use Groshy\Message\Dto\PositionEvent\UpdatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\UpdateTransactionDto;
use Groshy\Presentation\Api\Dto\PositionEvent\ApiUpdatePositionEventDto;
use Webmozart\Assert\Assert;

class PositionEventUpdateDtoMapper extends CustomMapper
{
    public function __construct(
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function mapToObject($source, $destination)
    {
        /* @var ApiUpdatePositionEventDto $source */
        Assert::isInstanceOf($source, ApiUpdatePositionEventDto::class);
        /* @var UpdatePositionEventDto $destination */
        Assert::isInstanceOf($destination, UpdatePositionEventDto::class);

        if (!is_null($source->date)) {
            $destination->date = $source->date;
        }
        if (!is_null($source->notes)) {
            $destination->notes = $source->notes;
        }
        if (!is_null($source->type)) {
            $destination->type = PositionEventType::from($source->type);
        }
        if (!is_null($source->value)) {
            $destination->value = $this->mapper->map($source->value, PositionValueDto::class);
        }
        $destination->transactions = $this->mapper->mapMultiple($source->transactions, UpdateTransactionDto::class);

        return $destination;
    }
}
