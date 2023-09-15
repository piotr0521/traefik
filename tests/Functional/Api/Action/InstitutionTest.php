<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\User;
use Talav\Component\Resource\Repository\RepositoryInterface;

class InstitutionTest extends ApiTestCase
{
    private ?Generator $faker;

    private ?Client $client;

    private ?RepositoryInterface $institutionRepository;

    private ?User $testUser1;

    private const USER1 = 'user1';

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->client = static::createClient();

        $this->institutionRepository = $this->client->getContainer()->get('app.repository.institution');
        $userManager = $this->client->getContainer()->get('app.manager.user');
        $this->testUser1 = $userManager->getRepository()->findOneBy(['username' => self::USER1]);
        $this->client->loginUser($this->testUser1);
    }

    /**
     * @test
     */
    public function it_filters_institutions_by_name(): void
    {
        $filter = 'fund';
        $this->client->request('GET', '/api/institutions?name='.$filter);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'hydra:totalItems' => $this->countInstitutionsByName($filter),
        ]);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_input(): void
    {
        $this->client->request('POST', '/api/institutions', ['json' => []]);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'name',
                    'message' => 'This value should not be blank.',
                ],
                1 => [
                    'propertyPath' => 'website',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_long_name(): void
    {
        $this->client->request('POST', '/api/institutions', ['json' => [
            'name' => $this->faker->realTextBetween(300, 350),
        ]]);
        $this->assertJsonContains([
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
    public function it_shows_errors_for_long_website(): void
    {
        $this->client->request('POST', '/api/institutions', ['json' => [
            'name' => $this->faker->company(),
            'website' => $this->faker->realTextBetween(300, 350),
        ]]);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'website',
                    'message' => 'This value is too long. It should have 250 characters or less.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_incorrectly_formatted_website(): void
    {
        $this->client->request('POST', '/api/institutions', ['json' => [
            'name' => $this->faker->company(),
            'website' => $this->faker->realTextBetween(10, 30),
        ]]);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'website',
                    'message' => 'This value is not a valid URL.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_new_institution(): void
    {
        $response = $this->client->request('POST', '/api/institutions', ['json' => [
            'name' => $this->faker->company(),
            'website' => 'https://'.$this->faker->domainName(),
        ]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/api/contexts/Institution',
            '@type' => 'Institution',
        ]);
    }

    private function countInstitutionsByName(string $name): int
    {
        return (int) $this->institutionRepository
            ->createQueryBuilder('ins')
            ->select('COUNT(DISTINCT ins.id)')
            ->andWhere('ins.name LIKE :name')
            ->setParameter('name', '%'.$name.'%')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
