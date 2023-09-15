<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\Subscription;

use Groshy\Entity\Price;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Symfony\Component\Validator\Constraints as Assert;

class ApiCreateSubscriptionDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    public ?Price $price = null;
}
