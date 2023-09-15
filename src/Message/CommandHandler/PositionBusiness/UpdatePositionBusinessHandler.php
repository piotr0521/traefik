<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionBusiness;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\PositionBusiness;
use Groshy\Message\Command\PositionBusiness\UpdatePositionBusinessCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class UpdatePositionBusinessHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionBusinessManager,
        private readonly ManagerInterface $assetBusinessManager,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdatePositionBusinessCommand $message): PositionBusiness
    {
        $position = $message->resource;
        $asset = $position->getAsset();
        $this->mapper->mapToObject($message->dto, $position);
        $this->mapper->mapToObject($message->dto, $asset);
        $this->assetBusinessManager->update($asset);
        $this->positionBusinessManager->update($position, true);

        return $position;
    }
}
