<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionCollectable;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\PositionCollectable;
use Groshy\Message\Command\PositionCollectable\CreatePositionCollectableCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CreatePositionCollectableHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionCollectableManager,
        private readonly RepositoryInterface $assetCollectableRepository,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(CreatePositionCollectableCommand $message): PositionCollectable
    {
        /** @var PositionCollectable $resource */
        $resource = $this->mapper->mapToObject($message->dto, $this->positionCollectableManager->create());
        $resource->setAsset($this->assetCollectableRepository->getCollectableAsset());
        $this->positionCollectableManager->update($resource, true);

        return $resource;
    }
}
