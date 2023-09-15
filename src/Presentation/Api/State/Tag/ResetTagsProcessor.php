<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\State\Tag;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Groshy\Message\Command\Tag\ResetTagsCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

class ResetTagsProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly RepositoryInterface $userRepository,
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $user = $this->userRepository->find($uriVariables['id']);
        $this->bus->dispatch(new ResetTagsCommand($user));
    }
}
