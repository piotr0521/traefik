<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionCash;

use DateTime;
use Groshy\Entity\AccountHolder;
use Groshy\Entity\AccountType;
use Groshy\Entity\Institution;
use Groshy\Entity\Tag;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Groshy\Validator\Constraints\Money;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreatePositionCashDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\NotBlank]
    public ?float $yield = null;

    #[Assert\NotBlank]
    public ?AccountType $accountType = null;

    #[Assert\NotBlank]
    public ?Institution $institution = null;

    #[Assert\NotBlank]
    public ?AccountHolder $accountHolder = null;

    #[Assert\NotBlank]
    #[Assert\Sequentially([
        new Money(),
        new Assert\GreaterThanOrEqual(0),
        new Assert\LessThanOrEqual(999999999),
    ])]
    public ?string $balance = null;

    #[Assert\NotBlank]
    // +1 day is required to avoid any timezone issues, user can be up to 24h ahead of the server time
    #[Assert\LessThanOrEqual(new DateTime('+1 day'))]
    public ?DateTime $balanceDate = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
