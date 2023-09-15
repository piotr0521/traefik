<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use DateTime;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\Institution;
use Groshy\Entity\positionMortgage;
use Groshy\Entity\Tag;
use Groshy\Entity\User;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionMortgageTest extends ApiTestCase
{
    use UsersAwareTrait;
    use PositionTestTrait;

    private ?Generator $faker;
    private ?Client $client;

    private ?ManagerInterface $positionInvestmentManager;
    private ?RepositoryInterface $positionInvestmentRepository;
    private ?RepositoryInterface $institutionRepository;
    private ?RepositoryInterface $tagRepository;
    private ?RepositoryInterface $assetCertificateDepositRepository;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->positionMortgageRepository = $this->client->getContainer()->get('app.repository.position_mortgage');
        $this->assetCertificateDepositRepository = $this->client->getContainer()->get('app.repository.liability_mortgage');
        $this->institutionRepository = $this->client->getContainer()->get('app.repository.institution');
        $this->tagRepository = $this->client->getContainer()->get('app.repository.tag');
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_dto(): void
    {
        $this->client->request('POST', '/api/position/mortgages', ['json' => []]);
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
                    'propertyPath' => 'terms',
                    'message' => 'This value should not be blank.',
                ],
                2 => [
                    'propertyPath' => 'interest',
                    'message' => 'This value should not be blank.',
                ],
                3 => [
                    'propertyPath' => 'mortgageDate',
                    'message' => 'This value should not be blank.',
                ],
                4 => [
                    'propertyPath' => 'mortgageAmount',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_error_for_long_name(): void
    {
        $this->client->request('POST', '/api/position/mortgages', ['json' => [
            'name' => $this->faker->realTextBetween(300, 350),
        ]]);
        self::assertJsonContains([
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
    public function it_shows_error_for_long_term(): void
    {
        $this->client->request('POST', '/api/position/mortgages', ['json' => [
            'terms' => 78,
        ]]);
        self::assertJsonContains([
            'violations' => [
                1 => [
                    'propertyPath' => 'terms',
                    'message' => 'This value should be less than or equal to 60.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_error_for_short_term(): void
    {
        $this->client->request('POST', '/api/position/mortgages', ['json' => [
            'terms' => 0,
        ]]);
        self::assertJsonContains([
            'violations' => [
                1 => [
                    'propertyPath' => 'terms',
                    'message' => 'This value should be greater than or equal to 1.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_error_for_future_mortgage_date(): void
    {
        $this->client->request('POST', '/api/position/mortgages', ['json' => [
            'mortgageDate' => (new DateTime('+1 month'))->format('Y-m-d'),
        ]]);
        self::assertJsonContains([
            'violations' => [
                3 => [
                    'propertyPath' => 'mortgageDate',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_new_position_mortgage(): void
    {
        $data = [
            'name' => $this->faker->company,
            'terms' => $this->faker->numberBetween(1, 24),
            'interest' => $this->faker->numberBetween(45, 400),
            'mortgageDate' => $this->faker->dateTimeBetween('-3 years', '-1 year')->format('Y-m-d'),
            'mortgageAmount' => strval($this->faker->numberBetween(1, 99) * 1000),
            'institution' => static::findIriBy(Institution::class, ['id' => $this->getRandomInstitution()->getId()]),
            'notes' => $this->faker->boolean ? $this->faker->text(200) : null,
            'tags' => $this->faker->boolean ?
                array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3))) : [],
        ];
        $this->client->request('POST', '/api/position/mortgages', ['json' => $data]);
        $this->assertResponseStatusCodeSame(201);
    }

    /**
     * @test
     */
    public function it_only_returns_positions_mortgage_created_by_the_current_user(): void
    {
        $this->trait_it_only_returns_positions_created_by_the_current_user('/api/position/mortgages', $this->positionMortgageRepository);
    }

    /**
     * @test
     */
    public function it_allows_to_get_mortgage_by_id(): void
    {
        $position = $this->positionMortgageRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('GET', '/api/position/mortgages/'.$position->getId());
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_404_for_property_created_by_another_user(): void
    {
        $position = $this->positionMortgageRepository->findBy(['createdBy' => $this->getUser('user6')])[0];
        $this->client->request('GET', '/api/position/mortgages/'.$position->getId());
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_name_in_update_dto(): void
    {
        $position = $this->positionMortgageRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('PATCH', '/api/position/mortgages/'.$position->getId(), [
            'json' => [
                'name' => '',
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'name',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_updates_position_mortgage(): void
    {
        $institution = $this->getRandomInstitution();
        $data = [
            'name' => $this->faker->company,
            'terms' => $this->faker->numberBetween(1, 24),
            'interest' => $this->faker->numberBetween(45, 400),
            'institution' => static::findIriBy(Institution::class, ['id' => $institution]),
            'notes' => $this->faker->text(200),
            'tags' => array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3))),
        ];

        $position = $this->positionMortgageRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('PATCH', '/api/position/mortgages/'.$position->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        self::assertResponseStatusCodeSame(200);
        /** @var positionMortgage $position */
        $position = $this->positionMortgageRepository->find($position->getId());
        self::assertEquals($data['name'], $position->getName());
        self::assertEquals($data['terms'], $position->getTerms());
        self::assertEquals($data['interest'], $position->getInterest());
        self::assertEquals($institution->getId(), $position->getAccount()->getInstitution()->getId());
        self::assertEquals($data['notes'], $position->getNotes());
    }

    /**
     * @test
     */
    public function it_deletes_position_mortgage(): void
    {
        $position = $this->getRandompositionMortgage($this->getUser('user2'));
        $id = $position->getId();
        $this->client->request('DELETE', '/api/position/mortgages/'.$position->getId());
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->positionMortgageRepository->find($id));
    }

    private function getRandomInstitution(): Institution
    {
        return $this->faker->randomElement($this->institutionRepository->findAll());
    }

    private function getRandomTags(User $user, int $count = 1): array
    {
        $tags = $this->tagRepository->findBy(['createdBy' => $user]);

        return $this->faker->randomElements($tags, $count);
    }

    private function getRandompositionMortgage(User $user): positionMortgage
    {
        $positions = $this->positionMortgageRepository->findBy(['createdBy' => $user]);

        return $positions[array_rand($positions)];
    }
}
