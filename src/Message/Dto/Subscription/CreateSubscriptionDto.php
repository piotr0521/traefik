<?php

declare(strict_types=1);

namespace Groshy\Message\Dto\Subscription;

use Groshy\Entity\Price;
use Talav\Component\User\Model\UserInterface;

class CreateSubscriptionDto
{
    public function __construct(Price $price, UserInterface $createdBy)
    {
        $this->price = $price;
        $this->createdBy = $createdBy;
    }

    public ?Price $price = null;

    public ?UserInterface $createdBy = null;
}
