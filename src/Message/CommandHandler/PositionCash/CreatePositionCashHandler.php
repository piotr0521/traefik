<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PositionCash;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\Account;
use Groshy\Entity\PositionCash;
use Groshy\Message\Command\PositionCash\CreatePlaidPositionCashCommand;
use Groshy\Message\Command\PositionCash\CreatePositionCashCommand;
use Groshy\Message\Command\PositionEvent\CreatePositionEventCommand;
use Groshy\Message\Dto\PositionCash\CreatePositionCashDto;
use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\PositionValueDto;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class CreatePositionCashHandler implements MessageSubscriberInterface
{
    public function __construct(
        private readonly ManagerInterface $positionCashManager,
        private readonly ManagerInterface $accountManager,
        private readonly RepositoryInterface $assetCashRepository,
        private readonly AutoMapperInterface $mapper,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public static function getHandledMessages(): iterable
    {
        yield CreatePositionCashCommand::class => ['method' => 'createPositionCash'];
        yield CreatePlaidPositionCashCommand::class => ['method' => 'createPlaidPositionCash'];
    }

    public function createPositionCash(CreatePositionCashCommand $message): PositionCash
    {
        $resource = $this->createPosition($message->dto);
        $resource->setAccount($this->getAccount($message->dto));
        $this->positionCashManager->update($resource, true);

        return $resource;
    }

    public function createPlaidPositionCash(CreatePlaidPositionCashCommand $message): PositionCash
    {
        throw new \RuntimeException('Not tested. Probably broken');
        $resource = $this->createPosition($message->dto);
        $this->positionCashManager->update($resource, true);

        return $resource;
    }

    private function createPosition(CreatePositionCashDto $dto): PositionCash
    {
        /** @var PositionCash $resource */
        $position = $this->mapper->mapToObject($dto, $this->positionCashManager->create());
        $position->setName(null);
        $position->setAsset($this->assetCashRepository->getCashAsset());
        $position->getData()->setYield($dto->yield);
        $this->positionCashManager->update($position, true);
        if (null !== $dto->balance) {
            $this->messageBus->dispatch(new CreatePositionEventCommand(CreatePositionEventDto::factory(
                date: $dto->balanceDate,
                position: $position,
                value: PositionValueDto::factory(amount: $dto->balance),
                type: PositionEventType::BALANCE_UPDATE
            )));
        }

        return $position;
    }

    private function getAccount(CreatePositionCashDto $dto): ?Account
    {
        return $this->accountManager->getAccount($dto->createdBy, $dto->institution, $dto->accountType, $dto->accountHolder, $dto->name);
    }
}
