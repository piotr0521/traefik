<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\PositionCrypto;

use AlphaVantage\Api\DigitalCurrency;
use AlphaVantage\Client as AlphaVantageClient;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\Transaction;
use Groshy\Message\Command\PositionCrypto\CreatePositionCryptoCommand;
use Groshy\Message\CommandHandler\PositionCrypto\CreatePositionCryptoHandler;
use Groshy\Message\Dto\PositionCrypto\CreatePositionCryptoDto;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class CreatePositionCryptoHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;
    use MoneyAwareTrait;

    private ?Generator $faker;

    private ?RepositoryInterface $tagRepository;
    private ?RepositoryInterface $transactionRepository;
    private ?RepositoryInterface $assetCryptoRepository;
    private ?CreatePositionCryptoHandler $createHandler;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->faker = FakerFactory::create();

        $this->tagRepository = static::getContainer()->get('app.repository.tag');
        $this->transactionRepository = static::getContainer()->get('app.repository.transaction');
        $this->assetCryptoRepository = static::getContainer()->get('app.repository.asset_crypto');
        $this->createHandler = static::getContainer()->get(CreatePositionCryptoHandler::class);
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_creates_position_crypto_and_buy_transaction_and_triggers_history_download(): void
    {
        $api = $this->createMock(DigitalCurrency::class);
        $client = $this->createMock(AlphaVantageClient::class);
        $client->expects($this->once())->method('__call')->willReturn($api);
        $api->expects($this->once())->method('digitalCurrencyDaily')->willReturn(['Time Series (Digital Currency Daily)' => []]);
        static::getContainer()->set(AlphaVantageClient::class, $client);

        $dto = new CreatePositionCryptoDto();
        $dto->createdBy = $this->getUser('user2');
        $dto->asset = $this->assetCryptoRepository->findOneBy(['symbol' => 'ADA']);
        $dto->tags = $this->getRandomTags($this->faker->numberBetween(1, 4));
        $dto->notes = $this->faker->text(200);
        $dto->quantity = $this->faker->numberBetween(5, 10);
        $dto->averagePrice = $this->fromMinor($this->faker->numberBetween(100, 300));
        $dto->purchaseDate = $this->faker->dateTimeBetween('-2 years', '-1 month');
        $position = $this->createHandler->__invoke(new CreatePositionCryptoCommand($dto));

        self::assertEquals($dto->createdBy, $position->getCreatedBy());
        self::assertEquals($dto->asset, $position->getAsset());
        self::assertSameSize($dto->tags, $position->getTags());
        self::assertEquals($dto->notes, $position->getNotes());

        /** @var array<Transaction> $transactions */
        $transactions = $this->transactionRepository->findBy(['position' => $position], ['amount.amount' => 'DESC']);
        self::assertCount(1, $transactions);
        self::assertEquals('BUY', $transactions[0]->getType()->getShortName());
        self::assertEquals($dto->averagePrice->multiply(strval($dto->quantity)), $transactions[0]->getAmount());
        self::assertEquals($dto->purchaseDate, $transactions[0]->getTransactionDate());
    }

    private function getRandomTags(int $counter): array
    {
        return $this->faker->randomElements($this->tagRepository->findBy(['createdBy' => $this->getUser('user2')]), $counter);
    }
}
