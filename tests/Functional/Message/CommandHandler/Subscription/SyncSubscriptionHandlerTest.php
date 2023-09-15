<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\Subscription;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Message\Command\Subscription\SyncSubscriptionCommand;
use Groshy\Message\CommandHandler\Subscription\SyncSubscriptionHandler;
use Groshy\Tests\Helper\UsersAwareTrait;
use Stripe\ApiRequestor;
use Stripe\HttpClient\CurlClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class SyncSubscriptionHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;

    private ?RepositoryInterface $userRepository;
    private ?SyncSubscriptionHandler $handler;

    private ?Generator $faker;

    protected function setUp(): void
    {
        $this->handler = static::getContainer()->get(SyncSubscriptionHandler::class);
        $this->userRepository = static::getContainer()->get('app.repository.user');
        $this->setUpUsers(static::getContainer());
        $this->faker = FakerFactory::create();
    }

    /**
     * @test
     */
    public function it_updates_subscription_status(): void
    {
        $json = file_get_contents(realpath(__DIR__.'/../../../../var/subscription-1.json'));
        $client = $this->getMockBuilder(CurlClient::class)
            ->onlyMethods(['request'])
            ->getMock();
        $client->expects($this->once())
            ->method('request')
            ->willReturn([$json, 200, []]);
        ApiRequestor::setHttpClient($client);
        $user = $this->getUser('user24');
        self::assertCount(1, $user->getRoles());
        $this->handler->__invoke(new SyncSubscriptionCommand('sub_1MkuntBckVgjYj7SdTmjALla'));
        $user = $this->userRepository->find($user->getId());
        self::assertCount(2, $user->getRoles());
    }
}
