<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\PlaidItem;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Message\Command\PlaidConnection\CreateConnectionCommand;
use Groshy\Message\CommandHandler\PlaidConnection\CreateConnectionHandler;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;
use TomorrowIdeas\Plaid\Plaid;

class CreateConnectionHandlerTest extends WebTestCase
{
    use UsersAwareTrait;

    private ?RepositoryInterface $institutionRepository;
    private ?CreateConnectionHandler $handler;
    private Plaid $plaid;
    private ?Generator $faker;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $client = static::createClient();

        $this->faker = FakerFactory::create();
        $this->setUpUsers(static::getContainer());

        $this->handler = static::getContainer()->get(CreateConnectionHandler::class);
        $this->institutionRepository = static::getContainer()->get('app.repository.institution');
        $this->plaid = static::getContainer()->get(Plaid::class);
        $client->loginUser($this->getUser('user2'));
    }

    /**
     * @test
     */
    public function it_creates_new_plaid_connection_for_provided_institution(): void
    {
        $institutions = $this->institutionRepository->getPlaidInstitutions();
        $institution = $this->faker->randomElement($institutions);
        // Get public token for testing
        $token = $this->plaid->sandbox->createPublicToken($institution->getPlaidId(), ['transactions'])->public_token;
        $connection = $this->handler->__invoke(new CreateConnectionCommand($token, $this->getUser('user2')->getId()));
        self::assertEquals($connection->getInstitution(), $institution);
    }
}
