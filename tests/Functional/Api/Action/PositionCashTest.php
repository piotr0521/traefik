<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\AccountHolder;
use Groshy\Entity\AccountType;
use Groshy\Entity\Institution;
use Groshy\Entity\PositionCash;
use Groshy\Entity\Tag;
use Groshy\Entity\User;
use Groshy\Tests\Helper\DataBuilder;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionCashTest extends ApiTestCase
{
    use UsersAwareTrait;
    use PositionTestTrait;
    use DataBuilder;

    private ?Generator $faker;

    private ?Client $client;

    private ?ManagerInterface $positionCashManager;

    private ?ManagerInterface $institutionManager;

    private ?ManagerInterface $tagManager;

    private ?RepositoryInterface $assetTypeAccountTypeRepository;
    private ?RepositoryInterface $assetTypeRepository;
    private ?RepositoryInterface $accountHolderRepository;
    private ?RepositoryInterface $positionCashRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->positionCashManager = $this->client->getContainer()->get('app.manager.position_cash');
        $this->institutionManager = $this->client->getContainer()->get('app.manager.institution');
        $this->tagManager = $this->client->getContainer()->get('app.manager.tag');
        $this->assetTypeAccountTypeRepository = $this->client->getContainer()->get('app.repository.asset_type_account_type');
        $this->assetTypeRepository = $this->client->getContainer()->get('app.repository.asset_type');
        $this->accountHolderRepository = $this->client->getContainer()->get('app.repository.account_holder');
        $this->positionCashRepository = $this->client->getContainer()->get('app.repository.position_cash');
    }

    /**
     * @test
     */
    public function it_only_returns_positions_cash_created_by_the_current_user(): void
    {
        $this->trait_it_only_returns_positions_created_by_the_current_user('/api/position/cash', $this->positionCashRepository);
    }

    /**
     * @test
     */
    public function it_reads_position_cash_by_id(): void
    {
        // user2 is a logged in user
        $position = $this->createCashPosition(
            user: $this->getUser('user2'),
            accountHolder: $this->createAccountHolder($this->getUser('user2')),
            institution: $this->getRandomInstitution()
        );

        $response = $this->client->request('GET', '/api/position/cash/'.$position->getId());
        self::assertResponseIsSuccessful();
        $data = $response->toArray(false);
        self::assertArrayHasKey('account', $data);
        self::assertArrayHasKey('accountType', $data['account']);
        self::assertArrayHasKey('name', $data['account']['accountType']);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_position_cash_dto(): void
    {
        $this->client->request('POST', '/api/position/cash', ['json' => []]);
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
                    'propertyPath' => 'yield',
                    'message' => 'This value should not be blank.',
                ],
                2 => [
                    'propertyPath' => 'accountType',
                    'message' => 'This value should not be blank.',
                ],
                3 => [
                    'propertyPath' => 'institution',
                    'message' => 'This value should not be blank.',
                ],
                4 => [
                    'propertyPath' => 'accountHolder',
                    'message' => 'This value should not be blank.',
                ],
                5 => [
                    'propertyPath' => 'balance',
                    'message' => 'This value should not be blank.',
                ],
                6 => [
                    'propertyPath' => 'balanceDate',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_new_position_cash(): void
    {
        $data = [
            'accountType' => static::findIriBy(AccountType::class, ['id' => $this->getRandomAccountType()->getId()]),
            'institution' => static::findIriBy(Institution::class, ['id' => $this->getRandomInstitution()->getId()]),
            'accountHolder' => static::findIriBy(AccountHolder::class, ['id' => $this->getAccountHolder($this->getUser('user2'))->getId()]),
            'notes' => $this->faker->text(200),
            'name' => $this->faker->text(200),
            'yield' => $this->faker->randomFloat(4, 0.001, 0.003),
            'balance' => strval($this->faker->numberBetween(0, 50000)),
            'balanceDate' => $this->faker->dateTimeBetween('-2 days', 'now')->format('Y-m-d'),
            'tags' => array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3))),
        ];
        $response = $this->client->request('POST', '/api/position/cash', ['json' => $data]);
        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains([
            '@context' => '/api/contexts/PositionCash',
            '@type' => 'PositionCash',
        ]);
        self::assertMatchesRegularExpression('~^/api/position/cash/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$~', $response->toArray()['@id']);
        /** @var PositionCash $position */
        $position = $this->positionCashManager->getRepository()->find($response->toArray()['id']);
        self::assertNotNull($position->getAccount());
        self::assertNotNull($position->getAccount()->getInstitution());
        self::assertNotNull($position->getNotes());
        self::assertNotNull($position->getName());
        self::assertNotNull($position->getYield());
        self::assertGreaterThanOrEqual(1, count($position->getTags()));
    }

    /**
     * @test
     */
    public function it_updates_position_cash(): void
    {
        $cash = $this->getRandomPositionCash($this->getUser('user2'));
        $data = [
            'name' => $this->faker->text(),
            'notes' => $this->faker->text(),
            'tags' => [],
        ];
        $this->client->request('PATCH', '/api/position/cash/'.$cash->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        $this->assertResponseStatusCodeSame(200);
        $this->positionCashManager->reload($cash);
        self::assertEquals($data['name'], $cash->getName());
        self::assertEquals($data['notes'], $cash->getNotes());
        self::assertCount(0, $cash->getTags());
    }

    /**
     * @test
     */
    public function it_deletes_position_cash(): void
    {
        $cash = $this->getRandomPositionCash($this->getUser('user2'));
        $id = $cash->getId();
        $this->client->request('DELETE', '/api/position/cash/'.$cash->getId());
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->positionCashManager->getRepository()->find($id));
    }

    private function getRandomPositionCash(User $user): PositionCash
    {
        $positions = $this->positionCashManager->getRepository()->findBy(['createdBy' => $user]);

        return $positions[array_rand($positions)];
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

    private function getRandomAccountType(): AccountType
    {
        $assetType = $this->assetTypeRepository->findOneBy(['name' => 'Cash']);
        $accountTypes = $this->assetTypeAccountTypeRepository->findBy(['assetType' => $assetType]);

        return $this->faker->randomElement($accountTypes)->getAccountType();
    }

    private function getAccountHolder(User $user): AccountHolder
    {
        return $this->accountHolderRepository->findOneBy(['createdBy' => $user]);
    }
}
