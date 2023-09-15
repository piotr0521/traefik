<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionLoan;

use DateTime;
use Groshy\Entity\Institution;
use Money\Money;
use Talav\Component\User\Model\UserInterface;

class CreatePositionLoanDto
{
    public ?string $name = null;

    public ?int $terms = null;

    public ?float $interest = null;

    public ?DateTime $loanDate = null;

    public ?Money $loanAmount = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    public ?array $tags = null;

    public ?UserInterface $createdBy = null;
}
