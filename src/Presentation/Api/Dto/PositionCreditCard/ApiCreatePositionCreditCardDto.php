<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionCreditCard;

use Groshy\Entity\AccountHolder;
use Groshy\Entity\Institution;
use Groshy\Entity\Tag;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Groshy\Validator\Constraints\Money;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreatePositionCreditCardDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\Sequentially([
        new Money(),
        new Assert\GreaterThan(0.01),
    ])]
    public ?string $cardLimit = null;

    #[Assert\Sequentially([
        new Money(),
        new Assert\GreaterThanOrEqual(0),
    ])]
    public string $balance = '0';

    #[Assert\NotBlank]
    public ?Institution $institution = null;

    #[Assert\NotBlank]
    public ?AccountHolder $accountHolder = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
