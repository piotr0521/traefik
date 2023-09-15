<?php

declare(strict_types=1);

namespace Groshy\Application\Manager;

use Groshy\Domain\Enum\AccountSync;
use Groshy\Entity\Account;
use Groshy\Entity\AccountHolder;
use Groshy\Entity\AccountType;
use Groshy\Entity\Institution;
use Talav\Component\Resource\Manager\ResourceManager;
use Talav\Component\User\Model\UserInterface;

class AccountManager extends ResourceManager
{
    public function getAccount(
        UserInterface $user,
        ?Institution $institution = null,
        ?AccountType $accountType = null,
        ?AccountHolder $accountHolder = null,
        ?string $name = null
    ): ?Account {
        if (is_null($institution) || is_null($accountType) || is_null($accountHolder)) {
            return null;
        }
        $account = $this->getRepository()->getManualAccount($user, $institution, $accountType, $accountHolder);
        if (is_null($account)) {
            /** @var Account $account */
            $account = $this->create();
            $account->setName($name);
            $account->setAccountType($accountType);
            $account->setInstitution($institution);
            $account->setAccountSync(AccountSync::MANUAL);
            $account->setCreatedBy($user);
            $this->update($account);
        }

        return $account;
    }
}
