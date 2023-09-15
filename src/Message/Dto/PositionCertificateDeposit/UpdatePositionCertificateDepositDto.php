<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCertificateDeposit;

use Groshy\Entity\Institution;

class UpdatePositionCertificateDepositDto
{
    public ?string $name = null;

    public ?int $terms = null;

    public ?float $yield = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    public ?array $tags = null;
}
