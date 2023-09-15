<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\Sponsor;

use Groshy\Domain\Enum\Privacy;
use Talav\Component\User\Model\UserInterface;

class CreateSponsorDto
{
    public ?string $name = null;

    public ?string $website = null;

    public Privacy $privacy = Privacy::PRIVATE;

    public ?UserInterface $createdBy = null;
}
