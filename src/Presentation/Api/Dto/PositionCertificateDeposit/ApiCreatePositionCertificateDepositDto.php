<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionCertificateDeposit;

use DateTime;
use Groshy\Entity\Institution;
use Groshy\Entity\Tag;
use Groshy\Presentation\Api\Dto\CreatedByInjectable;
use Groshy\Validator\Constraints\Money;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Talav\Component\User\Model\UserInterface;

class ApiCreatePositionCertificateDepositDto implements CreatedByInjectable
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(1)]
    #[Assert\LessThanOrEqual(60)]
    public ?int $terms = null;

    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    public ?float $yield = null;

    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(new DateTime())]
    public ?DateTime $depositDate = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Sequentially([
            new Money(),
            new Assert\LessThanOrEqual(999999999),
        ]),
    ])]
    public ?string $depositValue = null;

    public ?Institution $institution = null;

    public ?string $notes = null;

    /**
     * @var array<Tag>
     */
    public array $tags = [];

    #[Ignore]
    public ?UserInterface $createdBy = null;
}
