<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\AssetInvestment;
use Groshy\Entity\Institution;
use Groshy\Entity\PositionInvestment;
use Groshy\Entity\TagGroup;
use Groshy\Message\Command\AssetInvestment\CreateAssetInvestmentCommand;
use Groshy\Message\Command\CreateResourceCommand;
use Groshy\Message\Command\PositionInvestment\CreatePositionInvestmentCommand;
use Groshy\Message\Dto\AssetInvestment\CreateAssetInvestmentDto;
use Groshy\Message\Dto\Institution\CreateInstitutionDto;
use Groshy\Message\Dto\PositionInvestment\CreatePositionInvestmentDto;
use Groshy\Message\Dto\TagGroup\CreateTagGroupDto;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Talav\Component\Registry\Registry\ServiceRegistryInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Model\ResourceInterface;

final class CreateResourceHandler implements MessageSubscriberInterface
{
    // @todo there should be a way to get this map from API platform internals
    private array $config = [
        CreateTagGroupDto::class => TagGroup::class,
        CreateInstitutionDto::class => Institution::class,
        CreatePositionInvestmentDto::class => PositionInvestment::class,
        CreateAssetInvestmentDto::class => AssetInvestment::class,
    ];

    public function __construct(
        private readonly AutoMapperInterface $mapper,
        private readonly ServiceRegistryInterface $managerRegistry,
    ) {
    }

    public static function getHandledMessages(): iterable
    {
        yield CreateResourceCommand::class;
        yield CreatePositionInvestmentCommand::class => ['method' => 'handleCreatePositionInvestmentCommand'];
        yield CreateAssetInvestmentCommand::class => ['method' => 'handleCreateAssetInvestmentCommand'];
    }

    public function handleCreatePositionInvestmentCommand(CreatePositionInvestmentCommand $message): PositionInvestment
    {
        return $this->process($message->dto, $this->managerRegistry->get(PositionInvestment::class));
    }

    public function handleCreateAssetInvestmentCommand(CreateAssetInvestmentCommand $message): AssetInvestment
    {
        return $this->process($message->dto, $this->managerRegistry->get(AssetInvestment::class));
    }

    public function __invoke(CreateResourceCommand $message): ResourceInterface
    {
        return $this->process($message->dto, $this->managerRegistry->get($this->config[get_class($message->dto)]));
    }

    private function process(mixed $dto, ManagerInterface $manager): ResourceInterface
    {
        $resource = $this->mapper->mapToObject($dto, $manager->create());
        $manager->update($resource, true);

        return $resource;
    }
}
