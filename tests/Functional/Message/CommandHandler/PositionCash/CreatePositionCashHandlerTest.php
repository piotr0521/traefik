<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\PositionCash;

use DateTime;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Entity\Institution;
use Groshy\Message\Command\PositionCash\CreatePositionCashCommand;
use Groshy\Message\CommandHandler\PositionCash\CreatePositionCashHandler;
use Groshy\Model\MoneyAwareTrait;
use Groshy\Tests\Helper\DataBuilder;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class CreatePositionCashHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;
    use MoneyAwareTrait;
    use DataBuilder;

    private ?Generator $faker;

    private ?RepositoryInterface $assetTypeRepository;
    private ?RepositoryInterface $institutionRepository;
    private ?RepositoryInterface $accountTypeRepository;
    private ?CreatePositionCashHandler $createPositionCashHandler;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();

        $this->institutionRepository = static::getContainer()->get('app.repository.institution');
        $this->accountTypeRepository = static::getContainer()->get('app.repository.account_type');
        $this->createPositionCashHandler = static::getContainer()->get(CreatePositionCashHandler::class);
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_creates_position_cash_and_account_if_institution_provided(): void
    {
        $dto = $this->createCashPositionDto(
            user: $this->getUser('user2'),
            institution: $this->getRandomInstitution(),
        );
        $position = $this->createPositionCashHandler->createPositionCash(new CreatePositionCashCommand($dto));

        self::assertEquals($dto->createdBy, $position->getCreatedBy());
        self::assertNotNull($position->getAsset());
        self::assertSameSize($dto->tags, $position->getTags());
        self::assertEquals($dto->notes, $position->getNotes());
    }

    /**
     * @test
     */
    public function it_creates_position_cash_and_position_value_with_correct_date(): void
    {
        $dto = $this->createCashPositionDto(
            user: $this->getUser('user2'),
            balanceDate: new DateTime('-1 day')
        );
        $position = $this->createPositionCashHandler->createPositionCash(new CreatePositionCashCommand($dto));
        self::assertEquals($dto->balanceDate->format('Y-m-d'), $position->getLastValue()->getDate()->format('Y-m-d'));
    }

    private function getRandomInstitution(): Institution
    {
        return $this->faker->randomElement($this->institutionRepository->findAll());
    }
}
