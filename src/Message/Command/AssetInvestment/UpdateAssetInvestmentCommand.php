<?php

declare(strict_types=1);

namespace Groshy\Message\Command\AssetInvestment;

use Groshy\Entity\AssetInvestment;
use Groshy\Message\Dto\AssetInvestment\UpdateAssetInvestmentDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class UpdateAssetInvestmentCommand implements DomainEventInterface
{
    public function __construct(
        public AssetInvestment $resource,
        public UpdateAssetInvestmentDto $dto
    ) {
    }
}
