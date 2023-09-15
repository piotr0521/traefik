<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\TagGroup;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class TagGroupTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;

    private ?Generator $faker;

    private RepositoryInterface $tagGroupRepository;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->tagGroupRepository = $this->client->getContainer()->get('app.repository.tag_group');
    }

    /**
     * @test
     */
    public function it_only_returns_tag_groups_created_by_the_current_user(): void
    {
        $result = $this->client->request('GET', '/api/tag_groups');
        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => count($this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')])),
        ]);
        foreach ($result->toArray()['hydra:member'] as $group) {
            $tagGroupDb = $this->tagGroupRepository->find($group['id']);
            self::assertEquals($tagGroupDb->getCreatedBy(), $this->getUser('user2'));
        }
    }

    /**
     * @test
     */
    public function it_allows_to_get_tag_group_by_id(): void
    {
        $tagGroup = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('GET', '/api/tag_groups/'.$tagGroup->getId());
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_404_for_tag_group_created_by_another_user(): void
    {
        $tagGroup = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user1')])[0];
        $this->client->request('GET', '/api/tag_groups/'.$tagGroup->getId());
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_dto(): void
    {
        $this->client->request('POST', '/api/tag_groups', ['json' => []]);
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
    public function it_shows_errors_for_long_name_in_create_dto(): void
    {
        $this->client->request('POST', '/api/tag_groups', ['json' => [
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
    public function it_shows_errors_for_negative_position_in_create_dto(): void
    {
        $this->client->request('POST', '/api/tag_groups', ['json' => [
            'name' => $this->faker->realTextBetween(100, 150),
            'position' => -1,
        ]]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'position',
                    'message' => 'This value should be greater than or equal to 0.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_too_large_position_in_create_dto(): void
    {
        $this->client->request('POST', '/api/tag_groups', ['json' => [
            'name' => $this->faker->realTextBetween(100, 150),
            'position' => 999999,
        ]]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'position',
                    'message' => 'This value should be less than or equal to 9999.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_new_tag_group(): void
    {
        $response = $this->client->request('POST', '/api/tag_groups', ['json' => [
            'name' => $this->faker->realTextBetween(100, 150),
            'position' => 0,
        ]]);
        self::assertResponseStatusCodeSame(201);
        self::assertMatchesRegularExpression('~^/api/tag_groups/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$~', $response->toArray()['@id']);
        self::assertMatchesResourceItemJsonSchema(TagGroup::class);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_name_in_update_dto(): void
    {
        $tagGroup = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('PATCH', '/api/tag_groups/'.$tagGroup->getId(), [
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
    public function it_updates_tag_group(): void
    {
        $tagGroup = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $newName = 'Updated '.$tagGroup->getName();
        $this->client->request('PATCH', '/api/tag_groups/'.$tagGroup->getId(), [
            'json' => [
                'name' => $newName,
                'position' => 99,
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        self::assertResponseStatusCodeSame(200);
        $tagGroup = $this->tagGroupRepository->find($tagGroup->getId());
        self::assertEquals($newName, $tagGroup->getName());
        self::assertEquals(1, $tagGroup->getPosition());
    }

    /**
     * @test
     */
    public function it_deletes_tag_group(): void
    {
        $tagGroup = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $id = $tagGroup->getId();
        $this->client->request('DELETE', '/api/tag_groups/'.$id);
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->tagGroupRepository->find($id));
    }
}
