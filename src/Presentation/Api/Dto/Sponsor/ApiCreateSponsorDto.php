<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\Sponsor;

use Groshy\Domain\Enum\Privacy;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreateSponsorDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\Length(max: 250)]
    #[Assert\Url]
    public ?string $website = null;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [Privacy::class, 'choices'])]
    public ?string $privacy = null;

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
