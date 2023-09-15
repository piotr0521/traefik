<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\AccountHolder;
use Groshy\Entity\Institution;
use Groshy\Entity\positionCreditCard;
use Groshy\Entity\Tag;
use Groshy\Entity\User;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Tests\Helper\DataBuilder;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionCreditCardTest extends ApiTestCase
{
    use UsersAwareTrait;
    use MoneyAwareTrait;
    use PositionTestTrait;
    use DataBuilder;

    private ?Generator $faker;

    private ?Client $client;

    private ?ManagerInterface $positionCreditCardManager;

    private ?ManagerInterface $institutionManager;

    private ?ManagerInterface $tagManager;
    private ?RepositoryInterface $accountHolderRepository;
    private ?RepositoryInterface $positionCreditCardRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->accountHolderRepository = $this->client->getContainer()->get('app.repository.account_holder');
        $this->positionCreditCardManager = $this->client->getContainer()->get('app.manager.position_credit_card');
        $this->positionCreditCardRepository = $this->client->getContainer()->get('app.repository.position_credit_card');
        $this->institutionManager = $this->client->getContainer()->get('app.manager.institution');
        $this->tagManager = $this->client->getContainer()->get('app.manager.tag');
    }

    /**
     * @test
     */
    public function it_only_returns_positions_credit_cards_created_by_the_current_user(): void
    {
        $this->trait_it_only_returns_positions_created_by_the_current_user('/api/position/credit_cards', $this->positionCreditCardRepository);
    }

    /**
     * @test
     */
    public function it_reads_position_credit_card_by_id(): void
    {
        // user2 is a logged in user
        $position = $this->createCreditCardPosition(user: $this->getUser('user2'));

        $this->client->request('GET', '/api/position/credit_cards/'.$position->getId());
        $this->assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_404_for_credit_cards_created_by_another_user(): void
    {
        // user1 is NOT a logged in user
        $position = $this->createCreditCardPosition(user: $this->getUser('user1'));
        $this->client->request('GET', '/api/position/credit_cards/'.$position->getId()->__toString());
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_credit_card_dto(): void
    {
        $this->client->request('POST', '/api/position/credit_cards', ['json' => []]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'name',
                    'message' => 'This value should not be blank.',
                ],
                1 => [
                    'propertyPath' => 'institution',
                    'message' => 'This value should not be blank.',
                ],
                2 => [
                    'propertyPath' => 'accountHolder',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_long_name_in_create_credit_card_dto(): void
    {
        $this->client->request('POST', '/api/position/credit_cards', ['json' => [
            'name' => $this->faker->realTextBetween(300, 350),
        ]]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'name',
                    'message' => 'This value is too long. It should have 250 characters or less.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_incorrect_money_value_for_card_limit_in_create_credit_card_dto(): void
    {
        $this->client->request('POST', '/api/position/credit_cards', ['json' => [
            'name' => $this->faker->realTextBetween(10, 35),
            'cardLimit' => '1,2',
        ]]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'cardLimit',
                    'message' => 'This value is not a correct amount.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_incorrect_money_value_for_balance_in_create_credit_card_dto(): void
    {
        $this->client->request('POST', '/api/position/credit_cards', ['json' => [
            'name' => $this->faker->realTextBetween(10, 35),
            'cardLimit' => '200',
            'balance' => '1,2',
        ]]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'balance',
                    'message' => 'This value is not a correct amount.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_new_position_credit_card(): void
    {
        $response = $this->client->request('POST', '/api/position/credit_cards', ['json' => [
            'cardLimit' => strval($this->faker->numberBetween(25, 50) * 1000),
            'balance' => strval($this->faker->numberBetween(0, 50000)),
            'institution' => static::findIriBy(Institution::class, ['id' => $this->getRandomInstitution()->getId()]),
            'accountHolder' => static::findIriBy(AccountHolder::class, ['id' => $this->getAccountHolder($this->getUser('user2'))->getId()]),
            'notes' => $this->faker->text(200),
            'name' => $this->faker->text(200),
            'tags' => array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3))),
        ]]);
        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains([
            '@context' => '/api/contexts/PositionCreditCard',
            '@type' => 'PositionCreditCard',
        ]);
        /** @var positionCreditCard $position */
        $position = $this->positionCreditCardManager->getRepository()->find($response->toArray()['id']);
        self::assertNotNull($position->getCardLimit());
        self::assertNotNull($position->getAccount());
        self::assertNotNull($position->getAccount()->getInstitution());
        self::assertNotNull($position->getNotes());
        self::assertNotNull($position->getName());
        self::assertNotNull($position->getLastValue());
        self::assertGreaterThanOrEqual(1, count($position->getTags()));
    }

    /**
     * @test
     */
    public function it_updates_position_credit_card(): void
    {
        $position = $this->createCreditCardPosition(user: $this->getUser('user2'));
        $newName = $this->faker->text(200);
        $newLimit = strval($this->faker->numberBetween(51, 99) * 1000);
        $this->client->request('PATCH', '/api/position/credit_cards/'.$position->getId(), [
            'json' => [
                'cardLimit' => $newLimit,
                'name' => $newName,
                'tags' => [],
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        $this->assertResponseStatusCodeSame(200);
        $this->positionCreditCardManager->reload($position);
        self::assertEquals(floatval($newLimit * 100), floatval($position->getCardLimit()->getAmount()));
        self::assertEquals($newName, $position->getName());
        self::assertCount(0, $position->getTags());
    }

    /**
     * @test
     */
    public function it_deletes_position_credit_card(): void
    {
        $position = $this->createCreditCardPosition(user: $this->getUser('user2'));
        $id = $position->getId();
        $this->client->request('DELETE', '/api/position/credit_cards/'.$position->getId());
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->positionCreditCardManager->getRepository()->find($id));
    }

    private function getRandomInstitution(): Institution
    {
        $institutions = $this->institutionManager->getRepository()->findAll();

        return $institutions[array_rand($institutions)];
    }

    private function getRandomTags(User $user, int $count = 1): array
    {
        $tags = $this->tagManager->getRepository()->findBy(['createdBy' => $user]);

        return $this->faker->randomElements($tags, $count);
    }

    private function getAccountHolder(User $user): AccountHolder
    {
        return $this->accountHolderRepository->findOneBy(['createdBy' => $user]);
    }
}
