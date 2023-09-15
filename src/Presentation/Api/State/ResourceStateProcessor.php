<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use AutoMapperPlus\AutoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Groshy\Presentation\Api\Dto\IdInjectable;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Security\Core\Security;
use Talav\Component\Resource\Model\ResourceInterface;

class ResourceStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
        private readonly MessageBusInterface $bus,
        private readonly AutoMapperInterface $mapper,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // if previous abject is a resource and DTO needs id, copy it
        $object = $context['previous_data'] ?? false;
        if ($object instanceof ResourceInterface && $data instanceof IdInjectable) {
            $data->id = $object->getId();
        }
        if ($data instanceof CreatedByInjectable) {
            $data->createdBy = $this->security->getUser();
        }
        $this->validator->validate($data);

        switch ($operation->getMethod()) {
            case 'PATCH': return $this->processPatch($data, $operation, $context);
            case 'POST': return $this->processPost($data, $operation);
            case 'DELETE': return $this->processDelete($operation, $context);
        }
        throw new \RuntimeException('Unknown item operation');
    }

    public function processPatch($data, Operation $operation, array $context = []): ResourceInterface
    {
        $transform = $operation->getInput()['transform'];
        $entity = $this->em->find($context['resource_class'], $context['previous_data']->getId());
        $dto = $this->mapper->map($data, $transform['dto']);
        $envelope = $this->bus->dispatch(new $transform['command']($entity, $dto));
        // get the value that was returned by the last message handler
        return $envelope->last(HandledStamp::class)->getResult();
    }

    public function processPost($data, Operation $operation): ResourceInterface
    {
        $transform = $operation->getInput()['transform'];
        $dto = $this->mapper->map($data, $transform['dto']);
        $envelope = $this->bus->dispatch(new $transform['command']($dto));
        // get the value that was returned by the last message handler
        return $envelope->last(HandledStamp::class)->getResult();
    }

    public function processDelete(Operation $operation, array $context = []): void
    {
        $transform = $operation->getInput()['transform'];
        $entity = $this->em->find($context['resource_class'], $context['previous_data']->getId());
        $this->validator->validate($entity, ['groups' => 'delete']);
        $this->bus->dispatch(new $transform['command']($entity));
    }
}
