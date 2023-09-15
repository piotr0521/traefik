<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionCreditCard;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\PositionCreditCard;
use Groshy\Message\Command\PositionCreditCard\UpdatePositionCreditCardCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class UpdatePositionCreditCardHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $positionCreditCardManager,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdatePositionCreditCardCommand $message): PositionCreditCard
    {
        $position = $message->resource;
        $this->mapper->mapToObject($message->dto, $position);
        $this->positionCreditCardManager->update($position, true);

        return $position;
    }
}
