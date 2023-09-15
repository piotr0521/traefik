<?php

declare(strict_types=1);

namespace Groshy\Tests\Helper;

use DateTime;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\PositionEventType;
use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\AccountHolder;
use Groshy\Entity\AssetInvestment;
use Groshy\Entity\Institution;
use Groshy\Entity\Position;
use Groshy\Entity\PositionCash;
use Groshy\Entity\PositionCreditCard;
use Groshy\Entity\PositionEvent;
use Groshy\Entity\PositionInvestment;
use Groshy\Entity\Sponsor;
use Groshy\Message\Command\AccountHolder\CreateAccountHolderCommand;
use Groshy\Message\Command\AssetInvestment\CreateAssetInvestmentCommand;
use Groshy\Message\Command\PositionCash\CreatePositionCashCommand;
use Groshy\Message\Command\PositionCreditCard\CreatePositionCreditCardCommand;
use Groshy\Message\Command\PositionEvent\CreatePositionEventCommand;
use Groshy\Message\Command\PositionEvent\DeletePositionEventCommand;
use Groshy\Message\Command\PositionInvestment\CreatePositionInvestmentCommand;
use Groshy\Message\Command\Sponsor\CreateSponsorCommand;
use Groshy\Message\CommandHandler\AccountHolder\CreateAccountHolderHandler;
use Groshy\Message\CommandHandler\CreateResourceHandler;
use Groshy\Message\CommandHandler\PositionCash\CreatePositionCashHandler;
use Groshy\Message\CommandHandler\PositionCreditCard\CreatePositionCreditCardHandler;
use Groshy\Message\CommandHandler\PositionEvent\CreatePositionEventHandler;
use Groshy\Message\CommandHandler\PositionEvent\DeletePositionEventHandler;
use Groshy\Message\CommandHandler\Sponsor\CreateSponsorHandler;
use Groshy\Message\Dto\AccountHolder\CreateAccountHolderDto;
use Groshy\Message\Dto\AssetInvestment\CreateAssetInvestmentDto;
use Groshy\Message\Dto\PositionCash\CreatePositionCashDto;
use Groshy\Message\Dto\PositionCreditCard\CreatePositionCreditCardDto;
use Groshy\Message\Dto\PositionEvent\CreatePositionEventDto;
use Groshy\Message\Dto\PositionEvent\PositionValueDto;
use Groshy\Message\Dto\PositionInvestment\CreatePositionInvestmentDto;
use Groshy\Message\Dto\Sponsor\CreateSponsorDto;
use Money\Money;
use Talav\Component\User\Model\UserInterface;

trait DataBuilder
{
    protected function createInvestmentPosition(
        UserInterface $user,
        ?string $typeName = null,
        ?Money $capitalCommitment = null
    ): PositionInvestment {
        if (is_null($typeName)) {
            $typeName = 'Hard Money Loan Fund';
        }
        $type = $this->getContainer()->get('app.repository.asset_type')->findOneBy(['name' => $typeName]);
        $dto = new CreatePositionInvestmentDto();
        $dto->asset = $this->getContainer()->get('app.repository.asset')->findOneBy(['assetType' => $type]);
        $dto->createdBy = $user;
        if (!is_null($capitalCommitment)) {
            $dto->capitalCommitment = $capitalCommitment;
        }

        return $this->getContainer()->get(CreateResourceHandler::class)->handleCreatePositionInvestmentCommand(new CreatePositionInvestmentCommand($dto));
    }

    protected function createCashPosition(
        UserInterface $user,
        ?AccountHolder $accountHolder = null,
        ?Institution $institution = null,
        ?string $accountTypeName = null,
        ?string $name = null,
        ?string $notes = null,
        ?float $yield = null,
        ?Money $currentValue = null,
    ): PositionCash {
        $dto = $this->createCashPositionDto($user, $accountHolder, $institution, $accountTypeName, $name, $notes, $yield, $currentValue);

        return $this->getContainer()->get(CreatePositionCashHandler::class)->createPositionCash(new CreatePositionCashCommand($dto));
    }

    protected function createCashPositionDto(
        UserInterface $user,
        ?AccountHolder $accountHolder = null,
        ?Institution $institution = null,
        ?string $accountTypeName = null,
        ?string $name = null,
        ?string $notes = null,
        ?float $yield = null,
        ?Money $balance = null,
        ?DateTime $balanceDate = new DateTime(),
    ): CreatePositionCashDto {
        if (is_null($accountTypeName)) {
            $accountTypeName = 'Checking';
        }
        $dto = new CreatePositionCashDto();
        $dto->accountType = $this->getContainer()->get('app.repository.account_type')->findOneBy(['name' => $accountTypeName]);
        $dto->createdBy = $user;
        $dto->accountHolder = $accountHolder;
        $dto->institution = $institution;
        $dto->name = $name ?? $this->getFaker()->company().' cash account';
        $dto->notes = $notes ?? $this->getFaker()->text();
        $dto->yield = $yield ?? $this->getFaker()->randomFloat(2, 0, 1) / 10;
        $dto->balance = $balance ?? Money::USD($this->getFaker()->numberBetween(10000, 20000));
        $dto->balanceDate = $balanceDate;

        return $dto;
    }

    protected function createCreditCardPosition(
        UserInterface $user,
        ?string $name = null
    ): PositionCreditCard {
        $dto = new CreatePositionCreditCardDto();
        $dto->createdBy = $user;
        $dto->name = $name ?? $this->getFaker()->company().' credit card';

        return $this->getContainer()->get(CreatePositionCreditCardHandler::class)->__invoke(new CreatePositionCreditCardCommand($dto));
    }

    protected function createPositionEvent(
        Position $position,
        ?DateTime $date = null,
        ?Money $valueAmount = null,
        ?PositionEventType $type = null,
        ?string $notes = null,
        array $transactions = []
    ): PositionEvent {
        $dto = new CreatePositionEventDto();
        if (is_null($date)) {
            $date = new DateTime();
        }
        if (!is_null($valueAmount)) {
            $positionValueDto = new PositionValueDto();
            $positionValueDto->amount = $valueAmount;
            $dto->value = $positionValueDto;
        }

        $dto->date = $date;
        $dto->position = $position;
        $dto->type = $type;
        $dto->notes = $notes;
        $dto->transactions = $transactions;

        return $this->getContainer()->get(CreatePositionEventHandler::class)->__invoke(new CreatePositionEventCommand($dto));
    }

    protected function createSponsor(
        UserInterface $user,
        string $name = null,
        Privacy $privacy = Privacy::PRIVATE,
        string $website = null,
    ): Sponsor {
        $dto = new CreateSponsorDto();
        $dto->createdBy = $user;
        $dto->name = $name ?? $this->faker->company();
        $dto->privacy = $privacy;
        $dto->website = $website ?? $this->faker->domainName();

        return $this->getContainer()->get(CreateSponsorHandler::class)->__invoke(new CreateSponsorCommand($dto));
    }

    protected function createAssetInvestment(
        UserInterface $user,
        string $name,
        Sponsor $sponsor,
        string $typeName,
        Privacy $privacy = Privacy::PUBLIC,
    ): AssetInvestment {
        $dto = new CreateAssetInvestmentDto();
        $dto->createdBy = $user;
        $dto->name = $name;
        $dto->privacy = $privacy;
        $dto->sponsor = $sponsor;
        $dto->assetType = $this->getContainer()->get('app.repository.asset_type')->findOneBy(['name' => $typeName]);

        return $this->getContainer()->get(CreateResourceHandler::class)->handleCreateAssetInvestmentCommand(new CreateAssetInvestmentCommand($dto));
    }

    protected function createAccountHolder(
        UserInterface $user,
        ?string $name = null,
    ): AccountHolder {
        $dto = new CreateAccountHolderDto();
        $dto->name = $name ?? $this->faker->name();
        $dto->createdBy = $user;

        return $this->getContainer()->get(CreateAccountHolderHandler::class)->__invoke(new CreateAccountHolderCommand($dto));
    }

    protected function deletePositionEvent(PositionEvent $positionEvent)
    {
        return $this->getContainer()->get(DeletePositionEventHandler::class)->__invoke(new DeletePositionEventCommand($positionEvent));
    }

    protected function getFaker(): Generator
    {
        return FakerFactory::create();
    }
}
