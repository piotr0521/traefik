<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionCash;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\PositionCash;
use Groshy\Message\Command\PositionCash\UpdatePositionCashCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class UpdatePositionCashHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionCashManager,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdatePositionCashCommand $message): PositionCash
    {
        $position = $message->resource;
        $this->mapper->mapToObject($message->dto, $position);
        // always use account name
        $position->setName(null);
        if (!is_null($message->dto->name)) {
            $position->getAccount()->setName($message->dto->name);
        }
        $position->getData()->setYield($message->dto->yield);
        $this->positionCashManager->update($position, true);

        return $position;
    }
}
