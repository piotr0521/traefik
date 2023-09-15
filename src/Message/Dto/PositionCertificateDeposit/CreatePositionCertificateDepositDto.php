<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionCertificateDeposit;

use DateTime;
use Groshy\Entity\Institution;
use Money\Money;
use Talav\Component\User\Model\UserInterface;

class CreatePositionCertificateDepositDto
{
    public ?string $name = null;

    public ?int $terms = null;

    public ?float $yield = null;

    public ?DateTime $depositDate = null;

    public ?Money $depositValue = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    public array $tags = [];

    public ?UserInterface $createdBy = null;
}
