<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\Subscription;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Groshy\Presentation\Api\State\Subscription\CreateSubscriptionProcessor;

#[ApiResource(operations: [
    new Get(controller: NotFoundAction::class, output: false, read: false),
    new Post(input: ['class' => ApiCreateSubscriptionDto::class], processor: CreateSubscriptionProcessor::class),
])]
class Subscription
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $clientSecret;
}
