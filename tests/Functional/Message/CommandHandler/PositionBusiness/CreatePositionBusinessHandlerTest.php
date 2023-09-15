<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\PositionBusiness;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Enum\TransactionTypeKey;
use Groshy\Message\Command\PositionBusiness\CreatePositionBusinessCommand;
use Groshy\Message\CommandHandler\CreateResourceHandler;
use Groshy\Message\CommandHandler\PositionBusiness\CreatePositionBusinessHandler;
use Groshy\Message\Dto\PositionBusiness\CreatePositionBusinessDto;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class CreatePositionBusinessHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;
    use MoneyAwareTrait;

    private ?Generator $faker;

    private ?RepositoryInterface $assetTypeRepository;
    private ?RepositoryInterface $tagRepository;
    private ?RepositoryInterface $transactionRepository;
    private ?CreateResourceHandler $createResourceHandler;
    private ?CreatePositionBusinessHandler $createHandler;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->faker = FakerFactory::create();

        $this->assetTypeRepository = static::getContainer()->get('app.repository.asset_type');
        $this->tagRepository = static::getContainer()->get('app.repository.tag');
        $this->transactionRepository = static::getContainer()->get('app.repository.transaction');
        $this->createHandler = static::getContainer()->get(CreatePositionBusinessHandler::class);
        $this->createResourceHandler = static::getContainer()->get(CreateResourceHandler::class);
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_creates_position_business_and_two_transactions(): void
    {
        $dto = new CreatePositionBusinessDto();
        $dto->createdBy = $this->getUser('user2');
        $dto->name = $this->faker->realTextBetween(10, 30);
        $dto->description = $this->faker->realTextBetween(100, 500);
        $dto->website = $this->faker->url();
        $dto->ownership = $this->faker->numberBetween(50, 100);
        $dto->tags = $this->getRandomTags($this->faker->numberBetween(1, 4));
        $dto->notes = $this->faker->text(200);
        $dto->originalValue = $this->fromBase($this->faker->numberBetween(400, 500) * 1000);
        $dto->originalDate = $this->faker->dateTimeBetween('-5 years', '-1 year');
        $dto->currentValue = $dto->originalValue->multiply(strval($this->faker->randomFloat(2, 1.05, 1.20)));
        $dto->valueDate = $this->faker->dateTimeBetween('-2 months', '-1 day');
        $position = $this->createHandler->__invoke(new CreatePositionBusinessCommand($dto));

        self::assertEquals($dto->createdBy, $position->getCreatedBy());
        self::assertNotNull($position->getAsset());
        self::assertSameSize($dto->tags, $position->getTags());
        self::assertEquals($dto->notes, $position->getNotes());

        $transactions = $this->transactionRepository->findBy(['position' => $position], ['amount.amount' => 'DESC']);
        self::assertCount(2, $transactions);
        self::assertEquals(TransactionTypeKey::VALUE_UPDATE, $transactions[0]->getType()->getShortName());
        self::assertEquals($dto->currentValue, $transactions[0]->getAmount());
        self::assertEquals('BUY', $transactions[1]->getType()->getShortName());
        self::assertEquals($dto->originalValue, $transactions[1]->getAmount());
        self::assertEquals($dto->originalDate, $transactions[1]->getTransactionDate());
    }

    private function getRandomTags(int $counter): array
    {
        return $this->faker->randomElements($this->tagRepository->findBy(['createdBy' => $this->getUser('user2')]), $counter);
    }
}
