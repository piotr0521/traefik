<?php

declare(strict_types=1);

namespace Groshy\Presentation\Console\Command;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use Groshy\Config\ConfigProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(name: 'groshy:config:dump')]
final class DumpConfigCommand extends Command
{
    public function __construct(
        private readonly ConfigProvider $provider,
        private readonly SerializerInterface $serializer,
        private readonly ResourceMetadataCollectionFactoryInterface $metadataFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows to dump backend config for asset types')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileName = realpath(__DIR__.'/../../').'/assets/config/backend/asset_type.json';
        $data = $this->provider->toArray();
        foreach ($data as $element) {
            if (is_null($element->positionClass)) {
                continue;
            }
            $resource = $this->metadataFactory->create($element->positionClass)->getIterator()->current();
            $operation = $this->getOperation($resource);

            $url = '/api'.$operation->getRoutePrefix().$operation->getUriTemplate();
            $url = str_replace('.{_format}', '', $url);
            $element->positionUrl = $url;
        }
        file_put_contents($fileName, $this->serializer->serialize($this->provider->toArray(), 'json'));

        return Command::SUCCESS;
    }

    private function getOperation(ApiResource $resource)
    {
        /** @var Operation $operation */
        foreach ($resource->getOperations() as $operation) {
            if ('POST' == $operation->getMethod()) {
                return $operation;
            }
        }
    }
}
