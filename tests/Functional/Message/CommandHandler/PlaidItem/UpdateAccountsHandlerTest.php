<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\PlaidItem;

use Groshy\Entity\Account;
use Groshy\Entity\Position;
use Groshy\Message\Command\PlaidConnection\UpdateAccountsCommand;
use Groshy\Message\CommandHandler\PlaidConnection\UpdateAccountsHandler;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class UpdateAccountsHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;

    private ?RepositoryInterface $accountRepository;
    private ?RepositoryInterface $positionRepository;
    private ?UpdateAccountsHandler $handler;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->setUpUsers(static::getContainer());

        $this->handler = static::getContainer()->get(UpdateAccountsHandler::class);
        $this->accountRepository = static::getContainer()->get('app.repository.account');
        $this->positionRepository = static::getContainer()->get('app.repository.position');
    }

    /**
     * @test
     */
    public function it_updates_accounts(): void
    {
        /** @var array<Account> $accounts */
        $accounts = $this->accountRepository->findBy(['createdBy' => $this->getUser('user2')]);
        $command = new UpdateAccountsCommand($this->getUser('user2')->getId());
        $this->handler->__invoke($command);
        foreach ($accounts as $account) {
            if (!$account->isDepository() || 'cd' == $account->getAccountType()->getPlaidName()) {
                continue;
            }
            /** @var Position $position */
            $position = $this->positionRepository->findOneBy(['account' => $account]);
            self::assertNotNull($position);
        }
    }
}
