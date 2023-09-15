<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\PositionBusiness;

use DateTime;
use Money\Money;
use Talav\Component\User\Model\UserInterface;

class CreatePositionBusinessDto
{
    public ?string $name = null;

    public ?string $description = null;

    public ?string $website = null;

    public ?float $ownership = null;

    public ?DateTime $originalDate = null;

    public ?Money $originalValue = null;

    public ?Money $currentValue = null;

    public ?DateTime $valueDate = null;

    public ?string $notes = null;

    public array $tags = [];

    public ?UserInterface $createdBy = null;
}
