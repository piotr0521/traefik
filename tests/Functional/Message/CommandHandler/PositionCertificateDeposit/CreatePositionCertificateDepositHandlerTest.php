<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\PositionCertificateDeposit;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\Institution;
use Groshy\Enum\TransactionTypeKey;
use Groshy\Message\Command\PositionCertificateDeposit\CreatePositionCertificateDepositCommand;
use Groshy\Message\CommandHandler\PositionCertificateDeposit\CreatePositionCertificateDepositHandler;
use Groshy\Message\Dto\PositionCertificateDeposit\CreatePositionCertificateDepositDto;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class CreatePositionCertificateDepositHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;
    use MoneyAwareTrait;

    private ?Generator $faker;

    private ?RepositoryInterface $assetTypeRepository;
    private ?RepositoryInterface $tagRepository;
    private ?RepositoryInterface $institutionRepository;
    private ?RepositoryInterface $transactionRepository;
    private ?RepositoryInterface $assetCertificateDepositRepository;
    private ?CreatePositionCertificateDepositHandler $createHandler;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->faker = FakerFactory::create();

        $this->assetTypeRepository = static::getContainer()->get('app.repository.asset_type');
        $this->tagRepository = static::getContainer()->get('app.repository.tag');
        $this->institutionRepository = static::getContainer()->get('app.repository.institution');
        $this->transactionRepository = static::getContainer()->get('app.repository.transaction');
        $this->assetCertificateDepositRepository = static::getContainer()->get('app.repository.asset_certificate_deposit');

        $this->createHandler = static::getContainer()->get(CreatePositionCertificateDepositHandler::class);
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_creates_position_property_and_two_transactions(): void
    {
        $dto = new CreatePositionCertificateDepositDto();
        $dto->createdBy = $this->getUser('user2');
        $dto->name = $this->faker->company();
        $dto->terms = $this->faker->numberBetween(1, 24);
        $dto->yield = $this->faker->numberBetween(45, 400);
        $dto->depositDate = $this->faker->dateTimeBetween('-3 years', '-1 year');
        $dto->depositValue = $this->fromBase($this->faker->numberBetween(1, 99) * 1000);
        $dto->asset = $this->assetCertificateDepositRepository->getCertificateDepositAsset();
        $dto->tags = $this->getRandomTags($this->faker->numberBetween(1, 4));
        $dto->notes = $this->faker->text(200);
        $dto->institution = $this->getRandomInstitution();
        $position = $this->createHandler->__invoke(new CreatePositionCertificateDepositCommand($dto));

        self::assertEquals($dto->createdBy, $position->getCreatedBy());
        self::assertEquals($dto->name, $position->getName());
        self::assertEquals($dto->terms, $position->getTerms());
        self::assertEquals($dto->yield, $position->getYield());
        self::assertEquals($dto->asset, $position->getAsset());
        self::assertSameSize($dto->tags, $position->getTags());
        self::assertEquals($dto->notes, $position->getNotes());

        $transactions = $this->transactionRepository->findBy(['position' => $position], ['amount.amount' => 'DESC']);
        self::assertCount(1, $transactions);
        self::assertEquals(TransactionTypeKey::DEPOSIT, $transactions[0]->getType()->getShortName());
        self::assertEquals($dto->depositValue, $transactions[0]->getAmount());
        self::assertEquals($dto->depositDate, $transactions[0]->getTransactionDate());
    }

    private function getRandomTags(int $counter): array
    {
        return $this->faker->randomElements($this->tagRepository->findBy(['createdBy' => $this->getUser('user2')]), $counter);
    }

    private function getRandomInstitution(): Institution
    {
        return $this->faker->randomElement($this->institutionRepository->findAll());
    }
}
