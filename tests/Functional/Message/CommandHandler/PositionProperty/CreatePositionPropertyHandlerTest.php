<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\PositionProperty;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\PropertyType;
use Groshy\Enum\TransactionTypeKey;
use Groshy\Message\Command\PositionProperty\CreatePositionPropertyCommand;
use Groshy\Message\CommandHandler\CreateResourceHandler;
use Groshy\Message\CommandHandler\PositionProperty\CreatePositionPropertyHandler;
use Groshy\Message\Dto\PositionProperty\CreatePositionPropertyDto;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class CreatePositionPropertyHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;
    use MoneyAwareTrait;

    private ?Generator $faker;

    private ?RepositoryInterface $assetTypeRepository;
    private ?RepositoryInterface $tagRepository;
    private ?RepositoryInterface $transactionRepository;
    private ?CreateResourceHandler $createResourceHandler;
    private ?CreatePositionPropertyHandler $createHandler;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->faker = FakerFactory::create();

        $this->assetTypeRepository = static::getContainer()->get('app.repository.asset_type');
        $this->tagRepository = static::getContainer()->get('app.repository.tag');
        $this->transactionRepository = static::getContainer()->get('app.repository.transaction');
        $this->createHandler = static::getContainer()->get(CreatePositionPropertyHandler::class);
        $this->createResourceHandler = static::getContainer()->get(CreateResourceHandler::class);
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_creates_position_property_and_two_transactions(): void
    {
        $dto = new CreatePositionPropertyDto();
        $dto->createdBy = $this->getUser('user2');
        $dto->name = $this->faker->realTextBetween(10, 150);
        $dto->propertyType = $this->faker->randomElement(PropertyType::cases());
        $dto->address = str_replace("\n", ' ', $this->faker->address());
        $dto->website = $this->faker->url();
        $dto->units = $this->faker->numberBetween(2, 450);
        $dto->tags = $this->getRandomTags($this->faker->numberBetween(1, 4));
        $dto->notes = $this->faker->text(200);
        $dto->purchaseValue = $this->fromBase($this->faker->numberBetween(400, 500) * 1000);
        $dto->currentValue = $dto->purchaseValue->multiply(strval(1 + $this->faker->randomFloat(2, 5, 20)));
        $dto->purchaseDate = $this->faker->dateTimeBetween('-5 years', '-1 year');
        $position = $this->createHandler->__invoke(new CreatePositionPropertyCommand($dto));

        self::assertEquals($dto->createdBy, $position->getCreatedBy());
        self::assertNotNull($position->getAsset());
        self::assertSameSize($dto->tags, $position->getTags());
        self::assertEquals($dto->notes, $position->getNotes());

        $transactions = $this->transactionRepository->findBy(['position' => $position], ['amount.amount' => 'DESC']);
        self::assertCount(2, $transactions);
        self::assertEquals(TransactionTypeKey::VALUE_UPDATE, $transactions[0]->getType()->getShortName());
        self::assertEquals($dto->currentValue, $transactions[0]->getAmount());
        self::assertEquals('BUY', $transactions[1]->getType()->getShortName());
        self::assertEquals($dto->purchaseValue, $transactions[1]->getAmount());
        self::assertEquals($dto->purchaseDate, $transactions[1]->getTransactionDate());
    }

    private function getRandomTags(int $counter): array
    {
        return $this->faker->randomElements($this->tagRepository->findBy(['createdBy' => $this->getUser('user2')]), $counter);
    }
}
