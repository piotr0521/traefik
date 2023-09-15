<?php

declare(strict_types=1);

namespace Groshy\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Groshy\Message\Command\PositionCertificateDeposit\CreatePositionCertificateDepositCommand;
use Groshy\Message\Command\PositionCertificateDeposit\DeletePositionCertificateDepositCommand;
use Groshy\Message\Command\PositionCertificateDeposit\UpdatePositionCertificateDepositCommand;
use Groshy\Message\Dto\PositionCertificateDeposit\CreatePositionCertificateDepositDto;
use Groshy\Message\Dto\PositionCertificateDeposit\UpdatePositionCertificateDepositDto;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionCertificateDeposit\ApiCreatePositionCertificateDepositDto;
use Groshy\Presentation\Api\Dto\PositionCertificateDeposit\ApiUpdatePositionCertificateDepositDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/certificate_deposits/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/certificate_deposits/{id}.{_format}', input: ['class' => ApiUpdatePositionCertificateDepositDto::class, 'transform' => ['dto' => UpdatePositionCertificateDepositDto::class, 'command' => UpdatePositionCertificateDepositCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/certificate_deposits/{id}.{_format}', input: ['transform' => ['command' => DeletePositionCertificateDepositCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/certificate_deposits.{_format}', input: ['class' => ApiCreatePositionCertificateDepositDto::class, 'transform' => ['dto' => CreatePositionCertificateDepositDto::class, 'command' => CreatePositionCertificateDepositCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/certificate_deposits.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position'
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionCertificateDeposit extends Position
{
    #[Column(type: 'object')]
    protected PositionCertificateDepositData $data;

    public function __construct()
    {
        $this->data = new PositionCertificateDepositData();
        parent::__construct();
    }

    public function getData(): PositionCertificateDepositData
    {
        return $this->data;
    }

    public function setData(PositionCertificateDepositData $data): void
    {
        $this->data = $data;
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getTerms(): ?int
    {
        return $this->data->getTerms();
    }

    #[Groups(['position:item:read', 'position:collection:read'])]
    public function getYield(): ?float
    {
        return $this->data->getYield();
    }
}
