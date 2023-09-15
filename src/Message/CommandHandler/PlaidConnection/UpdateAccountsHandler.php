<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\PlaidConnection;

use DateTime;
use Groshy\Domain\Enum\AccountSync;
use Groshy\Entity\Account;
use Groshy\Entity\AccountType;
use Groshy\Entity\PlaidConnection;
use Groshy\Enum\TransactionTypeKey;
use Groshy\Message\Command\PlaidConnection\UpdateAccountsCommand;
use Groshy\Message\Command\PositionCash\CreatePlaidPositionCashCommand;
use Groshy\Message\Command\Transaction\CreateTransactionCommand;
use Groshy\Message\Dto\PositionCash\CreatePlaidPositionCashDto;
use Groshy\Message\Dto\Transaction\CreateTransactionDto;
use Groshy\Model\MoneyAwareTrait;
use Money\Currency;
use Money\Money;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Talav\Component\User\Model\UserInterface;
use TomorrowIdeas\Plaid\Plaid;

final class UpdateAccountsHandler implements MessageHandlerInterface
{
    use MoneyAwareTrait;

    /** @var array<AccountType> */
    private array $accountTypes = [];

    public function __construct(
        private readonly ManagerInterface $accountManager,
        private readonly RepositoryInterface $plaidConnectionRepository,
        private readonly RepositoryInterface $accountTypeRepository,
        // private readonly RepositoryInterface $transactionTypeRepository,
        private readonly RepositoryInterface $positionRepository,
        private readonly Plaid $plaid,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(UpdateAccountsCommand $message): void
    {
        $filters['createdBy'] = $message->userId;
        if (!is_null($message->itemId)) {
            $filters['id'] = $message->itemId;
        }

        /** @var array<PlaidConnection> $items */
        $connections = $this->plaidConnectionRepository->findBy($filters);
        foreach ($connections as $connection) {
            $accounts = $this->plaid->accounts->list($connection->getAccessToken());
            foreach ($accounts->accounts as $plaidAccount) {
                /** @var Account $account */
                $account = $this->accountManager->getRepository()->findOneBy(['plaidId' => $plaidAccount->account_id]);
                if (is_null($account)) {
                    $account = $this->accountManager->create();
                    $account->setPlaidId($plaidAccount->account_id);
                    $account->setPlaidConnection($connection);
                }
                $account->setName($plaidAccount->name);
                $account->setOfficialName($plaidAccount->official_name);
                $account->setMask($plaidAccount->mask);
                $account->setAccountSync($this->mapUpdateType($accounts->item->update_type));
                $account->setAccountType($this->mapAccountType($plaidAccount->type, $plaidAccount->subtype));
                $account->setCreatedBy($connection->getCreatedBy());
                $account->setInstitution($connection->getInstitution());

                $this->accountManager->update($account, true);
                $position = $this->positionRepository->findOneBy(['account' => $account]);
                if (is_null($position)) {
                    $this->createPosition($plaidAccount, $account, $connection->getCreatedBy());
                }
            }
        }
        $this->accountManager->flush();
    }

    private function mapUpdateType($plaidUpdateType): AccountSync
    {
        if ('background' == $plaidUpdateType) {
            return AccountSync::PLAID_AUTO;
        }

        return AccountSync::PLAID_MANUAL;
    }

    private function mapAccountType(string $plaidType, ?string $plaidSubtype = null)
    {
        if (0 == count($this->accountTypes)) {
            $this->accountTypes = $this->accountTypeRepository->findAll();
        }
        foreach ($this->accountTypes as $type) {
            if ($plaidType == $type->getPlaidName()) {
                foreach ($type->getChildren() as $childType) {
                    if ($childType->getPlaidName() == $plaidSubtype) {
                        return $childType;
                    }
                }
            }
        }
        throw new \RuntimeException('Type is not found: '.$plaidType.', subtype: '.$plaidSubtype);
    }

    private function createPosition($plaidAccount, Account $account, UserInterface $user)
    {
        if ('depository' != $plaidAccount->type) {
            return;
        }
        if ('cd' === $plaidAccount->subtype) {
            return;
        }
        $this->createPositionCash($plaidAccount, $account, $user);
    }

    private function createPositionCash($plaidAccount, Account $account, UserInterface $user)
    {
        $dto = new CreatePlaidPositionCashDto();
        $dto->name = $plaidAccount->name;
        $dto->createdBy = $user;
        $dto->account = $account;
        $position = $this->bus->dispatch(new CreatePlaidPositionCashCommand($dto))->last(HandledStamp::class)->getResult();

        $dto = new CreateTransactionDto();
        $dto->position = $position;
        $dto->amount = new Money($plaidAccount->balances->available, $this->getCurrency($plaidAccount));
        $dto->type = $this->transactionTypeRepository->findOneBy(['shortName' => TransactionTypeKey::BALANCE_UPDATE]);
        $dto->transactionDate = new DateTime();
        $this->bus->dispatch(new CreateTransactionCommand($dto))->last(HandledStamp::class)->getResult();
    }

    private function getCurrency($plaidAccount): Currency
    {
        if (!is_null($plaidAccount->balances->iso_currency_code)) {
            return new Currency($plaidAccount->balances->iso_currency_code);
        }

        return new Currency($plaidAccount->balances->unofficial_currency_code);
    }
}
