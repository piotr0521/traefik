<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\Institution;

use Talav\Component\User\Model\UserInterface;

class CreateInstitutionDto
{
    public ?string $name = null;

    public ?string $website = null;

    public ?UserInterface $createdBy = null;
}
