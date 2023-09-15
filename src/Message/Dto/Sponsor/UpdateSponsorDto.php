<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\Sponsor;

use Groshy\Domain\Enum\Privacy;

class UpdateSponsorDto
{
    public ?string $name = null;

    public ?string $website = null;

    public ?Privacy $privacy = null;
}
