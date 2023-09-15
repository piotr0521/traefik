<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\Sponsor;

use Groshy\Domain\Enum\Privacy;
use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdateSponsorDto
{
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\Length(max: 250)]
    #[Assert\Url]
    public ?string $website = null;

    #[Assert\Choice(callback: [Privacy::class, 'choices'])]
    public ?string $privacy = null;
}
