<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\Sponsor;
use Groshy\Tests\Helper\DataBuilder;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class SponsorTest extends ApiTestCase
{
    use UsersAwareTrait;
    use DataBuilder;

    private ?Generator $faker;

    private ?Client $client;

    private ?RepositoryInterface $sponsorRepository;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());

        $this->sponsorRepository = $this->client->getContainer()->get('app.repository.sponsor');
        $this->client->loginUser($this->getUser('user2'));
    }

    /**
     * @test
     */
    public function it_filters_sponsors_by_privacy(): void
    {
        $this->client->request('GET', '/api/sponsors?privacy='.Privacy::PRIVATE->value);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'hydra:totalItems' => $this->countPrivateSponsors(),
        ]);
    }

    /**
     * @test
     */
    public function it_only_returns_public_and_private_sponsors_created_by_user(): void
    {
        $this->client->request('GET', '/api/sponsors');
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'hydra:totalItems' => $this->countPrivateSponsors() + $this->countPublicSponsors(),
        ]);
    }

    /**
     * @test
     */
    public function it_returns_404_for_private_sponsors_created_by_another_user(): void
    {
        $sponsor = $this->createSponsor(
            user: $this->getUser('user1'),
        );

        $this->client->request('GET', '/api/sponsors/'.$sponsor->getId());
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_returns_sponsor_entity(): void
    {
        $sponsor = $this->createSponsor(
            user: $this->getUser('user2'),
        );

        $this->client->request('GET', '/api/sponsors/'.$sponsor->getId());
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * @test
     */
    public function it_returns_correct_permissions_for_sponsor_created_by_the_same_user(): void
    {
        $sponsor = $this->createSponsor(
            user: $this->getUser('user2'),
        );

        $this->client->request('GET', '/api/sponsors/'.$sponsor->getId());
        self::assertJsonContains([
            'permissions' => [
                'canEdit' => true,
                'canDelete' => true,
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_returns_correct_permissions_for_public_sponsor_created_by_another_user(): void
    {
        $sponsor = $this->createSponsor(
            user: $this->getUser('user1'),
            privacy: Privacy::PUBLIC
        );

        $this->client->request('GET', '/api/sponsors/'.$sponsor->getId());
        self::assertJsonContains([
            'permissions' => [
                'canEdit' => false,
                'canDelete' => false,
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_returns_stats_for_sponsors(): void
    {
        $this->client->request('GET', '/api/sponsors/stats');
        $this->assertResponseStatusCodeSame(200);
        foreach ($this->client->getResponse()->toArray()['hydra:member'] as $dto) {
            self::assertArrayHasKey('sponsor', $dto);
            self::assertArrayHasKey('total', $dto);
            self::assertArrayHasKey('@type', $dto['sponsor']);
            self::assertArrayHasKey('@id', $dto['sponsor']);
            self::assertArrayHasKey('name', $dto['sponsor']);
            self::assertNotEmpty($dto['sponsor']['name']);
        }
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_dto(): void
    {
        $this->client->request('POST', '/api/sponsors', ['json' => []]);
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
                    'propertyPath' => 'privacy',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_long_name_in_create_dto(): void
    {
        $this->client->request('POST', '/api/sponsors', ['json' => [
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
    public function it_creates_new_sponsor(): void
    {
        $data = [
            'name' => $this->faker->realTextBetween(100, 150),
            'website' => 'https://'.$this->faker->domainName(),
            'privacy' => Privacy::PUBLIC->value,
        ];
        $response = $this->client->request('POST', '/api/sponsors', ['json' => $data]);
        self::assertResponseStatusCodeSame(201);
        /** @var Sponsor $sponsor */
        $sponsor = $this->sponsorRepository->find($response->toArray(false)['id']);
        self::assertEquals($data['name'], $sponsor->getName());
        self::assertEquals($data['website'], $sponsor->getWebsite());
        self::assertEquals($data['privacy'], $sponsor->getPrivacy()->value);
    }

    /**
     * @test
     */
    public function it_deletes_sponsor(): void
    {
        $sponsor = $this->createSponsor(
            user: $this->getUser('user2'),
            name: $this->faker->realTextBetween(100, 150)
        );

        $id = $sponsor->getId();
        $this->client->request('DELETE', '/api/sponsors/'.$id);
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->sponsorRepository->find($id));
    }

    /**
     * @test
     */
    public function it_does_not_allow_non_admin_to_delete_sponsor_created_by_another_user(): void
    {
        $sponsor = $this->createSponsor(
            user: $this->getUser('user3'),
            name: $this->faker->realTextBetween(100, 150)
        );

        $this->client->request('DELETE', '/api/sponsors/'.$sponsor->getId());
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_does_not_allow_to_delete_sponsor_with_attached_asset(): void
    {
        $sponsor = $this->createSponsor(
            user: $this->getUser('user2'),
            name: $this->faker->company()
        );

        $this->createAssetInvestment(
            user: $this->getUser('user2'),
            name: $this->faker->realTextBetween(100, 150),
            sponsor: $sponsor,
            typeName: 'Private Equity LP Fund',
        );

        // Should return an error because there is an asset created for sponsor
        $this->client->request('DELETE', '/api/sponsors/'.$sponsor->getId());
        $this->assertResponseStatusCodeSame(422);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => '',
                    'message' => 'Sponsor cannot be deleted',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_updates_sponsor(): void
    {
        $sponsor = $this->createSponsor(
            user: $this->getUser('user2'),
            privacy: Privacy::PUBLIC
        );

        $id = $sponsor->getId();
        $data = [
            'name' => $this->faker->realTextBetween(100, 150),
        ];
        $response = $this->client->request('PATCH', '/api/sponsors/'.$id, [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        $this->assertResponseStatusCodeSame(200);

        $sponsor = $this->sponsorRepository->find($id);
        self::assertNotNull($sponsor);
        self::assertEquals($data['name'], $sponsor->getName());
    }

    private function countPrivateSponsors(): int
    {
        return (int) $this->sponsorRepository
            ->createQueryBuilder('sponsor')
            ->select('COUNT(DISTINCT sponsor.id)')
            ->andWhere('sponsor.createdBy = :user')
            ->andWhere('sponsor.privacy = :privacy')
            ->setParameter('user', $this->getUser('user2'))
            ->setParameter('privacy', Privacy::PRIVATE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countPublicSponsors(): int
    {
        return (int) $this->sponsorRepository
            ->createQueryBuilder('sponsor')
            ->select('COUNT(DISTINCT sponsor.id)')
            ->andWhere('sponsor.privacy = :privacy')
            ->setParameter('privacy', Privacy::PUBLIC)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
