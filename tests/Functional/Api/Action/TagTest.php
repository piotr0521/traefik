<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\Color;
use Groshy\Entity\TagGroup;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class TagTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Client $client;

    private ?Generator $faker;

    private RepositoryInterface $tagRepository;
    private RepositoryInterface $tagGroupRepository;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->client = static::createClient();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->tagRepository = $this->client->getContainer()->get('app.repository.tag');
        $this->tagGroupRepository = $this->client->getContainer()->get('app.repository.tag_group');
    }

    /**
     * @test
     */
    public function it_only_returns_tag_created_by_the_current_user(): void
    {
        $result = $this->client->request('GET', '/api/tags');
        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => count($this->tagRepository->findBy(['createdBy' => $this->getUser('user2')])),
        ]);
        foreach ($result->toArray()['hydra:member'] as $group) {
            $tagDb = $this->tagRepository->find($group['id']);
            self::assertEquals($tagDb->getCreatedBy(), $this->getUser('user2'));
        }
    }

    /**
     * @test
     */
    public function it_allows_to_get_tag_group_by_id(): void
    {
        $tag = $this->tagRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('GET', '/api/tags/'.$tag->getId());
        self::assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_404_for_tag_created_by_another_user(): void
    {
        $tagGroup = $this->tagRepository->findBy(['createdBy' => $this->getUser('user1')])[0];
        $this->client->request('GET', '/api/tag_groups/'.$tagGroup->getId());
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_create_dto(): void
    {
        $this->client->request('POST', '/api/tags', ['json' => []]);
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
                    'propertyPath' => 'tagGroup',
                    'message' => 'This value should not be blank.',
                ],
                2 => [
                    'propertyPath' => 'color',
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
        $this->client->request('POST', '/api/tags', ['json' => [
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
    public function it_shows_errors_for_too_large_position_in_create_dto(): void
    {
        $this->client->request('POST', '/api/tags', ['json' => [
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
    public function it_shows_errors_for_invalid_color_in_create_dto(): void
    {
        $this->client->request('POST', '/api/tags', ['json' => [
            'name' => $this->faker->realTextBetween(100, 150),
            'position' => 1,
            'color' => '#xxxxxx',
        ]]);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                1 => [
                    'propertyPath' => 'color',
                    'message' => 'The value you selected is not a valid choice.',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_new_tag(): void
    {
        $response = $this->client->request('POST', '/api/tags', ['json' => [
            'name' => $this->faker->realTextBetween(100, 150),
            'position' => 0,
            'color' => COLOR::COLOR1,
            'tagGroup' => static::findIriBy(TagGroup::class, ['createdBy' => $this->getUser('user2')]),
        ]]);
        self::assertResponseStatusCodeSame(201);
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_name_in_update_dto(): void
    {
        $tag = $this->tagRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('PATCH', '/api/tags/'.$tag->getId(), [
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
    public function it_updates_tag(): void
    {
        $tag = $this->tagRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $newName = 'Updated '.$tag->getName();
        $this->client->request('PATCH', '/api/tags/'.$tag->getId(), [
            'json' => [
                'name' => $newName,
                'position' => 99,
            ],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        self::assertResponseStatusCodeSame(200);
        $tag = $this->tagRepository->find($tag->getId());
        self::assertEquals($newName, $tag->getName());
        $lastPosition = $this->tagRepository->findBy(['tagGroup' => $tag->getTagGroup()], ['position' => 'DESC'])[0]->getPosition();
        self::assertEquals($lastPosition, $tag->getPosition());
    }

    /**
     * @test
     */
    public function it_deletes_tag_group(): void
    {
        $tag = $this->tagRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $id = $tag->getId();
        $this->client->request('DELETE', '/api/tags/'.$id);
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->tagRepository->find($id));
    }
}
