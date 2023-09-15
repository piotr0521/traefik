<?php

declare(strict_types=1);

namespace Groshy\Presentation\Api\Dto\PositionEvent;

use DateTime;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Entity\Position;
use Groshy\Validator\Constraints\PositionEventTypeAllowed;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[PositionEventTypeAllowed(fieldMap: [
    'type' => 'type',
    'position' => 'position',
])]
#[Assert\When(
    expression: 'this.type == "VALUE_UPDATE"',
    constraints: [new Assert\Callback(
        callback: [ApiCreatePositionEventDto::class, 'validateValueUpdateTransactions'],
    )],
)]
#[Assert\When(
    expression: 'this.type == "REINVEST"',
    constraints: [new Assert\Callback(
        callback: [ApiCreatePositionEventDto::class, 'validateReinvestTransactions']
    ),
    ],
)]
#[Assert\When(
    expression: 'this.type == "COMPLETE"',
    constraints: [new Assert\Callback(
        callback: [ApiCreatePositionEventDto::class, 'validateCompleteTransactions']
    ),
    ],
)]
class ApiCreatePositionEventDto
{
    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(new DateTime())]
    public ?DateTime $date = null;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: [PositionEventType::class, 'choices'])]
    public ?string $type = null;

    #[Assert\NotBlank]
    public ?Position $position = null;

    // todo replace with enum(PositionEventType::VALUE_UPDATE) after SF6.3 is available
    #[Assert\Expression(
        expression: "this.type == 'VALUE_UPDATE' && this.value == null",
        message: 'Value is required for value update event type',
        negate: false
    )]
    #[Assert\Expression(
        expression: "this.type == 'COMPLETE' && this.value !== null",
        message: 'After completion investment should have zero value',
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
    #[Assert\Valid]
    /** @var array<ApiCreateTransactionDto> */
    public array $transactions = [];

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

    public static function validateCompleteTransactions($payload, ExecutionContextInterface $context)
    {
        /** @var ApiCreateTransactionDto $transaction */
        foreach ($payload->transactions as $transaction) {
            if ($transaction->amount < 0) {
                $context->buildViolation('Complete event can only have one positive transaction')
                    ->atPath('type')
                    ->addViolation();
            }
        }
    }
}
