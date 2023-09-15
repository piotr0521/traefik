<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\Year;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Groshy\Presentation\Api\State\Year\YearStatsProvider;

#[ApiResource(operations: [
    new Get(uriTemplate: '/years/{year}', requirements: ['year' => '\d+'], controller: NotFoundAction::class, output: false, read: false, ),
    new GetCollection(uriTemplate: '/years/stats', filters: [], provider: YearStatsProvider::class),
])]
class Year
{
    public function __construct(#[ApiProperty(identifier: true)] public int $year, public int $total)
    {
    }
}
