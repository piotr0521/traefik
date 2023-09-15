<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\AssetInvestment;
use Groshy\Entity\AssetType;
use Groshy\Entity\Institution;
use Groshy\Entity\PositionInvestment;
use Groshy\Entity\Tag;
use Groshy\Entity\User;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

class PositionInvestmentTest extends ApiTestCase
{
    use UsersAwareTrait;
    use PositionTestTrait;
    use MoneyAwareTrait;

    private ?Generator $faker;
    private ?Client $client;

    private ?ManagerInterface $positionInvestmentManager;
    private ?RepositoryInterface $positionInvestmentRepository;
    private ?RepositoryInterface $assetInvestmentRepository;
    private ?RepositoryInterface $assetTypeRepository;

    private ?ManagerInterface $institutionManager;

    private ?ManagerInterface $tagManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();

        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->positionInvestmentManager = $this->client->getContainer()->get('app.manager.position_investment');
        $this->positionInvestmentRepository = $this->client->getContainer()->get('app.repository.position_investment');
        $this->assetInvestmentRepository = $this->client->getContainer()->get('app.repository.asset_investment');
        $this->assetTypeRepository = $this->client->getContainer()->get('app.repository.asset_type');
        $this->institutionManager = $this->client->getContainer()->get('app.manager.institution');
        $this->tagManager = $this->client->getContainer()->get('app.manager.tag');
    }

    /**
     * @test
     */
    public function it_only_returns_positions_investment_created_by_the_current_user(): void
    {
        $this->trait_it_only_returns_positions_created_by_the_current_user('/api/position/investments', $this->positionInvestmentRepository);
    }

    /**
     * @test
     */
    public function it_reads_position_investment_by_id(): void
    {
        // user2 is a logged in user
        $investment = $this->getRandomPositionInvestment($this->getUser('user2'));

        $this->client->request('GET', '/api/position/investments/'.$investment->getId());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }

    /**
     * @test
     */
    public function it_returns_404_for_investments_created_by_another_user(): void
    {
        $investment = $this->getRandomPositionInvestment($this->getUser('user1'));
        $this->client->request('GET', '/api/position/investments/'.$investment->getId()->__toString());
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_filters_position_by_asset_type(): void
    {
        $type = $this->assetTypeRepository->findOneBy(['name' => 'Public Non Traded REIT']);
        $response = $this->client->request('GET', '/api/position/investments?assetType='.$type->getId());
        $this->assertResponseStatusCodeSame(200);
        self::assertEquals($this->countPositionByType($type), $response->toArray(false)['hydra:totalItems']);
    }

    /**
     * @test
     */
    public function it_creates_new_position_investment(): void
    {
        $asset = $this->faker->randomElement($this->assetInvestmentRepository->findBy(['privacy' => Privacy::PUBLIC]));
        $isDirect = $this->faker->boolean;
        $this->client->request('POST', '/api/position/investments', ['json' => [
            'capitalCommitment' => strval($this->faker->numberBetween(25, 50) * 1000 * 100),
            'isDirect' => $isDirect,
            'institution' => $isDirect ? null : static::findIriBy(Institution::class, ['id' => $this->getRandomInstitution()->getId()]),
            'asset' => static::findIriBy(AssetInvestment::class, ['id' => $asset->getId()]),
            'notes' => $this->faker->boolean ? $this->faker->text(200) : null,
            'tags' => $this->faker->boolean ?
                array_map(function ($el) {return static::findIriBy(Tag::class, ['id' => $el->getId()]); }, $this->getRandomTags($this->getUser('user2'), $this->faker->numberBetween(1, 3))) : [],
        ]]);
        $this->assertResponseStatusCodeSame(201);
    }

    /**
     * @test
     */
    public function it_updates_position_investment(): void
    {
        $investment = $this->getRandomPositionInvestment($this->getUser('user2'));
        $data = [
            'capitalCommitment' => $this->addAndFormatBase($investment->getData()->getCapitalCommitment(), 2000),
            'isDirect' => !$investment->getData()->isDirect(),
        ];
        $this->client->request('PATCH', '/api/position/investments/'.$investment->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        $this->assertResponseStatusCodeSame(200);
        $investment = $this->positionInvestmentManager->getRepository()->find($investment->getId());
        self::assertEquals($data['capitalCommitment'], $investment->getCapitalCommitment()['base']);
        self::assertEquals($data['isDirect'], $investment->getData()->isDirect());
    }

    /**
     * @test
     */
    public function it_deletes_position_investment(): void
    {
        $investment = $this->getRandomPositionInvestment($this->getUser('user2'));
        $id = $investment->getId();
        $this->client->request('DELETE', '/api/position/investments/'.$investment->getId());
        $this->assertResponseStatusCodeSame(204);
        self::assertNull($this->positionInvestmentManager->getRepository()->find($id));
    }

    private function getRandomPositionInvestment(User $user): PositionInvestment
    {
        $positions = $this->positionInvestmentManager->getRepository()->findBy(['createdBy' => $user]);

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

    private function countPositionByType(AssetType $type): int
    {
        return count($this->positionInvestmentRepository->byType($type, $this->getUser('user2')));
    }
}
