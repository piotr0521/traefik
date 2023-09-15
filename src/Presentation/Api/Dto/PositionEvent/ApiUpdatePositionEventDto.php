<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionEvent;

use DateTime;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Presentation\Api\Dto\IdInjectable;
use Groshy\Validator\Constraints\PositionEventTypeAllowed;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[PositionEventTypeAllowed(fieldMap: [
    'type' => 'type',
    'id' => 'id',
])]
#[Assert\When(
    expression: 'this.type == "VALUE_UPDATE"',
    constraints: [new Assert\Callback(
        callback: [ApiUpdatePositionEventDto::class, 'validateValueUpdateTransactions'],
    )],
)]
#[Assert\When(
    expression: 'this.type == "REINVEST"',
    constraints: [new Assert\Callback(
        callback: [ApiUpdatePositionEventDto::class, 'validateReinvestTransactions']
    )],
)]
class ApiUpdatePositionEventDto implements IdInjectable
{
    #[Assert\LessThanOrEqual(new DateTime())]
    public ?DateTime $date = null;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [PositionEventType::class, 'choices'])]
    public ?string $type = null;

    // todo replace with enum(PositionEventType::VALUE_UPDATE) after SF6.3 is available
    #[Assert\Expression(
        expression: "this.type == 'VALUE_UPDATE' && this.value == null",
        message: 'Value is required for value update event type',
        negate: false
    )]
    #[Assert\Valid]
    public ?ApiPositionValueDto $value = null;

    #[Assert\Length(max: 1000)]
    public ?string $notes = null;

    #[Assert\When(
        expression: 'this.type == "CONTRIBUTION"',
        constraints: [
            new Assert\Expression(
                expression: 'this.transactions != [] && this.transactions[0].amount < 0',
                message: 'Contribution event should have 1 negative transaction',
            ),
        ],
    )]
    #[Assert\When(
        expression: 'this.type == "DISTRIBUTION"',
        constraints: [
            new Assert\Expression(
                expression: 'this.transactions != [] && this.transactions[0].amount > 0',
                message: 'Distribution event should have 1 positive transaction',
            ),
        ],
    )]
    /** @var array<ApiUpdateTransactionDto> */
    public array $transactions = [];

    #[Ignore]
    public ?UuidInterface $id = null;

    public static function validateReinvestTransactions($payload, ExecutionContextInterface $context)
    {
        if (2 != count($payload->transactions)) {
            $context->buildViolation('Reinvest event should have 2 transactions: one positive and one negative')
                ->atPath('type')
                ->addViolation();
        }
    }

    public static function validateValueUpdateTransactions($payload, ExecutionContextInterface $context)
    {
        if (0 < count($payload->transactions)) {
            $context->buildViolation('Value update event should not have any transactions')
                ->atPath('type')
                ->addViolation();
        }
    }
}
