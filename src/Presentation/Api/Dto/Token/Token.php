<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\Token;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Groshy\Presentation\Api\Action\Token\TokenCreateAction;
use Groshy\Presentation\Api\Action\Token\TokenExchangeAction;

#[ApiResource(operations: [new Get(), new Get(uriTemplate: '/tokens/exchange/{link}', controller: TokenExchangeAction::class, read: false), new Post(controller: TokenCreateAction::class, input: ['class' => ApiCreateTokenDto::class]), new GetCollection()])]
class Token
{
    #[ApiProperty(identifier: true)]
    public string $link;
}
