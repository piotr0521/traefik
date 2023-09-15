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
use Groshy\Message\Command\PositionLoan\CreatePositionLoanCommand;
use Groshy\Message\Command\PositionLoan\DeletePositionLoanCommand;
use Groshy\Message\Command\PositionLoan\UpdatePositionLoanCommand;
use Groshy\Message\Dto\PositionLoan\CreatePositionLoanDto;
use Groshy\Message\Dto\PositionLoan\UpdatePositionLoanDto;
use Groshy\Presentation\Api\Doctrine\Orm\Filter\AssetTypeFilter;
use Groshy\Presentation\Api\Dto\PositionLoan\ApiCreatePositionLoanDto;
use Groshy\Presentation\Api\Dto\PositionLoan\ApiUpdatePositionLoanDto;
use Groshy\Presentation\Api\State\ResourceStateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(uriTemplate: '/loans/{id}.{_format}', normalizationContext: ['groups' => ['position:item:read'], 'swagger_definition_name' => 'Item Read']),
        new Patch(uriTemplate: '/loans/{id}.{_format}', input: ['class' => ApiUpdatePositionLoanDto::class, 'transform' => ['dto' => UpdatePositionLoanDto::class, 'command' => UpdatePositionLoanCommand::class]], processor: ResourceStateProcessor::class),
        new Delete(uriTemplate: '/loans/{id}.{_format}', input: ['transform' => ['command' => DeletePositionLoanCommand::class]], processor: ResourceStateProcessor::class),
        new Post(uriTemplate: '/loans.{_format}', input: ['class' => ApiCreatePositionLoanDto::class, 'transform' => ['dto' => CreatePositionLoanDto::class, 'command' => CreatePositionLoanCommand::class]], processor: ResourceStateProcessor::class),
        new GetCollection(uriTemplate: '/loans.{_format}', normalizationContext: ['groups' => ['position:collection:read'], 'swagger_definition_name' => 'Collection Read']), ],
    routePrefix: '/position'
)]
#[Entity]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['startDate' => 'DESC'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(filterClass: AssetTypeFilter::class, properties: ['path' => 'asset.assetType'])]
class PositionLoan extends Position
{
    #[Column(type: 'object')]
    protected PositionLoanData $data;

    public function __construct()
    {
        $this->data = new PositionLoanData();
        parent::__construct();
    }

    public function getData(): PositionLoanData
    {
        return $this->data;
    }

    public function setData(PositionLoanData $data): void
    {
        $this->data = $data;
    }

    #[Groups(['position:item:read'])]
    public function getTerms(): ?int
    {
        return $this->data->getTerms();
    }

    #[Groups(['position:item:read'])]
    public function getInterest(): ?float
    {
        return $this->data->getInterest();
    }
}
