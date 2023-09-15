<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\AccountHolder;

use Symfony\Component\Validator\Constraints as Assert;

class ApiUpdateAccountHolderDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $name = null;
}
