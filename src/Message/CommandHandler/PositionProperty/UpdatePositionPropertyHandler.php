<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionProperty;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\PositionProperty;
use Groshy\Message\Command\PositionProperty\UpdatePositionPropertyCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class UpdatePositionPropertyHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionPropertyManager,
        private readonly ManagerInterface $assetPropertyManager,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdatePositionPropertyCommand $message): PositionProperty
    {
        $position = $message->resource;
        $asset = $position->getAsset();
        $this->mapper->mapToObject($message->dto, $position);
        $this->mapper->mapToObject($message->dto, $asset);
        $this->assetPropertyManager->update($asset);
        $this->positionPropertyManager->update($position, true);

        return $position;
    }
}
