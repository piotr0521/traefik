<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Message\Command\AssetInvestment\UpdateAssetInvestmentCommand;
use Groshy\Message\Command\PositionCollectable\UpdatePositionCollectableCommand;
use Groshy\Message\Command\PositionCrypto\UpdatePositionCryptoCommand;
use Groshy\Message\Command\PositionInvestment\UpdatePositionInvestmentCommand;
use Groshy\Message\Command\PositionSecurity\UpdatePositionSecurityCommand;
use Groshy\Message\Command\UpdateResourceCommand;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Talav\Component\Registry\Registry\ServiceRegistryInterface;
use Talav\Component\Resource\Model\ResourceInterface;

final class UpdateResourceHandler implements MessageSubscriberInterface
{
    public function __construct(
        private readonly AutoMapperInterface $mapper,
        private readonly ServiceRegistryInterface $managerRegistry,
    ) {
    }

    public static function getHandledMessages(): iterable
    {
        yield UpdateResourceCommand::class;
        yield UpdatePositionCollectableCommand::class;
        yield UpdatePositionSecurityCommand::class;
        yield UpdatePositionCryptoCommand::class;
        yield UpdatePositionInvestmentCommand::class;
        yield UpdateAssetInvestmentCommand::class;
    }

    public function __invoke(mixed $message): ResourceInterface
    {
        return $this->process($message->dto, $message->resource);
    }

    private function process(mixed $dto, ResourceInterface $resource): ResourceInterface
    {
        $manager = $this->managerRegistry->get(get_class($resource));
        $this->mapper->mapToObject($dto, $resource);
        $manager->update($resource, true);

        return $resource;
    }
}
