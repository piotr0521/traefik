<?php

declare(strict_types=1);

namespace Groshy\Message\Command\AssetInvestment;

use Groshy\Message\Dto\AssetInvestment\CreateAssetInvestmentDto;
use Talav\Component\Resource\Model\DomainEventInterface;

final class CreateAssetInvestmentCommand implements DomainEventInterface
{
    public function __construct(
        public CreateAssetInvestmentDto $dto
    ) {
    }
}
