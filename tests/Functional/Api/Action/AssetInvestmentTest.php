<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Api\Action;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\AssetInvestment;
use Groshy\Entity\AssetProperty;
use Groshy\Entity\AssetType;
use Groshy\Entity\Sponsor;
use Groshy\Entity\User;
use Groshy\Tests\Helper\UsersAwareTrait;
use Talav\Component\Resource\Repository\RepositoryInterface;

class AssetInvestmentTest extends ApiTestCase
{
    use UsersAwareTrait;

    private ?Generator $faker;
    private ?Client $client;

    private ?RepositoryInterface $assetInvestmentRepository;
    private ?RepositoryInterface $sponsorRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->faker = FakerFactory::create();
        $this->setUpUsers($this->client->getContainer());
        $this->client->loginUser($this->getUser('user2'));

        $this->assetInvestmentRepository = $this->client->getContainer()->get('app.repository.asset_investment');
        $this->sponsorRepository = $this->client->getContainer()->get('app.repository.sponsor');
    }

    /**
     * @test
     */
    public function it_only_returns_public_and_private_investments_created_by_user(): void
    {
        $count = count($this->assetInvestmentRepository->findBy(['createdBy' => $this->getUser('user2'), 'privacy' => Privacy::PRIVATE])) +
            count($this->assetInvestmentRepository->findBy(['privacy' => Privacy::PUBLIC]));
        $result = $this->client->request('GET', '/api/asset/investments');
        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'hydra:totalItems' => $count,
        ]);
        foreach ($result->toArray()['hydra:member'] as $property) {
            /** @var AssetInvestment $asset */
            $asset = $this->assetInvestmentRepository->find($property['id']);
            if (Privacy::PRIVATE == $asset->getPrivacy()) {
                self::assertEquals($asset->getCreatedBy(), $this->getUser('user2'));
            }
        }
    }

    /**
     * @test
     */
    public function it_returns_404_for_private_asset_created_by_another_user(): void
    {
        $asset = $this->getRandomAssetInvestmentCreatedBy($this->getUser('user1'));
        $this->client->request('GET', '/api/asset/investments/'.$asset->getId());
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @test
     */
    public function it_filters_by_sponsor(): void
    {
        $asset = $this->getRandomAssetInvestmentCreatedBy($this->getUser('user2'));
        $response = $this->client->request('GET', '/api/asset/investments?sponsor='.$asset->getSponsor()->getId());
        self::assertGreaterThanOrEqual(1, $response->toArray(false)['hydra:totalItems']);
    }

    /**
     * @test
     */
    public function it_shows_error_when_asset_type_is_not_compatible_with_asset_class(): void
    {
        $sponsor = $this->getRandomSponsor();
        $this->client->request('POST', '/api/asset/investments', ['json' => [
            'name' => $this->faker->company,
            'privacy' => Privacy::PUBLIC,
            'sponsor' => self::findIriBy(Sponsor::class, ['id' => $sponsor->getId()]),
            'assetType' => self::findIriBy(AssetType::class, ['name' => 'Cash']),
        ]]);
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                0 => [
                    'propertyPath' => 'assetType',
                    'message' => 'This asset type is not compatible with provided asset or position',
                    'code' => null,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_creates_new_asset_investment(): void
    {
        $sponsor = $this->getRandomSponsor();
        $data = [
            'name' => $this->faker->company.' Investment fund '.$this->faker->numberBetween(1, 5),
            'privacy' => Privacy::PUBLIC,
            'assetType' => self::findIriBy(AssetType::class, ['name' => 'Real Estate LP Fund']),
            'website' => 'https://'.$this->faker->domainName(),
            'term' => '7-10',
            'irr' => '8-15',
            'multiple' => '2.0-2.2',
            'sponsor' => self::findIriBy(Sponsor::class, ['id' => $sponsor->getId()]),
        ];
        $response = $this->client->request('POST', '/api/asset/investments', ['json' => $data]);
        $this->assertResponseStatusCodeSame(201);
        $asset = $this->assetInvestmentRepository->find($response->toArray()['id']);
        $data['sponsor'] = $sponsor;
        $this->compareAssetAndData($asset, $data);
        self::assertEquals('Real Estate LP Fund', $asset->getAssetType()->getName());
    }

    /**
     * @test
     */
    public function it_shows_errors_for_empty_name_in_update_dto(): void
    {
        $asset = $this->assetInvestmentRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('PATCH', '/api/asset/investments/'.$asset->getId(), [
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
    public function it_updates_investment(): void
    {
        $sponsor = $this->getRandomSponsor();
        $data = [
            'name' => $this->faker->company.' Investment fund '.$this->faker->numberBetween(1, 5),
            'privacy' => $this->faker->randomElement(Privacy::cases()),
            'website' => $this->faker->url(),
            'term' => '7-10',
            'irr' => '8-15',
            'multiple' => '2.0-2.2',
            'sponsor' => self::findIriBy(Sponsor::class, ['id' => $sponsor->getId()]),
        ];
        $asset = $this->assetInvestmentRepository->findBy(['createdBy' => $this->getUser('user2')])[0];
        $this->client->request('PATCH', '/api/asset/investments/'.$asset->getId(), [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        self::assertResponseStatusCodeSame(200);
        /** @var AssetProperty $property */
        $asset = $this->assetInvestmentRepository->find($asset->getId());
        $data['sponsor'] = $sponsor;
        $this->compareAssetAndData($asset, $data);
    }

    private function compareAssetAndData(AssetInvestment $asset, array $data): void
    {
        self::assertEquals($data['name'], $asset->getName());
        self::assertEquals($data['privacy'], $asset->getPrivacy());
        self::assertEquals($data['website'], $asset->getWebsite());
        self::assertEquals($data['term'], $asset->getTerm());
        self::assertEquals($data['irr'], $asset->getIrr());
        self::assertEquals($data['multiple'], $asset->getMultiple());
        self::assertEquals($data['sponsor'], $asset->getSponsor());
    }

    private function getRandomAssetInvestmentCreatedBy(User $user)
    {
        return $this->assetInvestmentRepository
            ->createQueryBuilder('ai')
            ->select('ai')
            ->andWhere('ai.createdBy = :user')
            ->andWhere('ai.privacy = :privacy')
            ->andWhere('ai.sponsor IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('privacy', Privacy::PRIVATE)
            ->orderBy('ai.id')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    private function getRandomSponsor(): Sponsor
    {
        return $this->faker->randomElement($this->sponsorRepository->findBy(['privacy' => Privacy::PUBLIC]));
    }
}
