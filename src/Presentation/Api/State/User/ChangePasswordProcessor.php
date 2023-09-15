<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use Groshy\Presentation\Api\Dto\User\ApiChangePasswordDto;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Talav\Component\User\Message\Command\UpdatePasswordCommand;
use Webmozart\Assert\Assert;

final class ChangePasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly RepositoryInterface $userRepository,
        private readonly MessageBusInterface $bus,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        Assert::isInstanceOf($data, ApiChangePasswordDto::class);
        $this->validator->validate($data);
        $user = $this->userRepository->find($uriVariables['id']);
        $this->bus->dispatch(new UpdatePasswordCommand($user, $data->newPassword));
    }
}
