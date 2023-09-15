<?php

namespace Groshy\Mapper\Config;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use Groshy\Entity\AssetBusiness;
use Groshy\Entity\AssetProperty;
use Groshy\Mapper\Dto\PositionEventCreateDtoMapper;
use Groshy\Mapper\Dto\PositionEventUpdateDtoMapper;
use Groshy\Mapper\DtoToEntityMapper;
use Groshy\Mapper\Model\PositionDateToDashboardValueMapper;
use Groshy\Message\Dto\PositionBusiness\CreatePositionBusinessDto;
use Groshy\Message\Dto\PositionBusiness\UpdatePositionBusinessDto;
use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\CreateTransactionDto;
use Groshy\Message\Dto\PositionEvent\PositionValueDto;
use Groshy\Message\Dto\PositionEvent\UpdatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\UpdateTransactionDto;
use Groshy\Message\Dto\PositionProperty\CreatePositionPropertyDto;
use Groshy\Message\Dto\PositionProperty\UpdatePositionPropertyDto;
use Groshy\Model\DashboardValue;
use Groshy\Model\PositionDate;
use Groshy\Presentation\Api\Dto\PositionEvent\ApiCreatePositionEventDto;
use Groshy\Presentation\Api\Dto\PositionEvent\ApiCreateTransactionDto;
use Groshy\Presentation\Api\Dto\PositionEvent\ApiPositionValueDto;
use Groshy\Presentation\Api\Dto\PositionEvent\ApiUpdatePositionEventDto;
use Groshy\Presentation\Api\Dto\PositionEvent\ApiUpdateTransactionDto;

class MapperConfig implements AutoMapperConfiguratorInterface
{
    public function __construct(
        private readonly DtoToEntityMapper $customMapper,
        private readonly PositionEventCreateDtoMapper $positionValueCreateDtoMapper,
        private readonly PositionEventUpdateDtoMapper $positionValueUpdateDtoMapper,
        private readonly ResourceMetadataCollectionFactoryInterface $metadataFactory,
        private readonly ResourceNameCollectionFactoryInterface $collectionFactory,
    ) {
    }

    public function configure(AutoMapperConfigInterface $config): void
    {
        $pairs = [];
        foreach ($this->collectionFactory->create() as $class) {
            $pairs = array_merge($pairs, $this->extractClassMapPairs($class));
        }
        foreach ($pairs as $pair) {
            $config->registerMapping($pair[0], $pair[1])
                ->useCustomMapper($this->customMapper);
        }

        // there is one request to create/update position property and asset property, add pairs manually
        $config->registerMapping(CreatePositionPropertyDto::class, AssetProperty::class)->useCustomMapper($this->customMapper);
        $config->registerMapping(UpdatePositionPropertyDto::class, AssetProperty::class)->useCustomMapper($this->customMapper);

        $config->registerMapping(CreatePositionBusinessDto::class, AssetBusiness::class)->useCustomMapper($this->customMapper);
        $config->registerMapping(UpdatePositionBusinessDto::class, AssetBusiness::class)->useCustomMapper($this->customMapper);

        $config->registerMapping(PositionDate::class, DashboardValue::class)
            ->useCustomMapper(new PositionDateToDashboardValueMapper());

        // position event mapping
        $config->registerMapping(ApiCreatePositionEventDto::class, CreatePositionEventDto::class)->useCustomMapper($this->positionValueCreateDtoMapper);
        $config->registerMapping(ApiPositionValueDto::class, PositionValueDto::class)->useCustomMapper($this->customMapper);
        $config->registerMapping(ApiCreateTransactionDto::class, CreateTransactionDto::class)->useCustomMapper($this->customMapper);

        $config->registerMapping(ApiUpdatePositionEventDto::class, UpdatePositionEventDto::class)->useCustomMapper($this->positionValueUpdateDtoMapper);
        $config->registerMapping(ApiUpdateTransactionDto::class, UpdateTransactionDto::class)->useCustomMapper($this->customMapper);
    }

    private function extractClassMapPairs(string $class): array
    {
        $result = [];
        $metadata = $this->metadataFactory->create($class);
        foreach ($metadata->getIterator() as $resource) {
            /** @var HttpOperation $operation */
            foreach ($resource->getOperations() as $operation) {
                if (in_array($operation->getMethod(), [HttpOperation::METHOD_PATCH, HttpOperation::METHOD_POST]) && is_array($operation->getInput())) {
                    $result = array_merge($result, $this->extractOperationMapPairs($operation->getInput(), $class));
                }
            }
        }

        return $result;
    }

    private function extractOperationMapPairs(array $data, string $class): array
    {
        $result = [];
        if (isset($data['transform']['dto'])) {
            // API DTO to command DTO
            $result[] = [$data['class'], $data['transform']['dto']];
            // Command DTO to entity
            $result[] = [$data['transform']['dto'], $class];
        }

        return $result;
    }
}
