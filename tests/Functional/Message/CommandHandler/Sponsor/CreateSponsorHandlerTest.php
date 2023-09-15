<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\Sponsor;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Groshy\Domain\Enum\Privacy;
use Groshy\Message\Command\Sponsor\CreateSponsorCommand;
use Groshy\Message\CommandHandler\Sponsor\CreateSponsorHandler;
use Groshy\Message\Dto\Sponsor\CreateSponsorDto;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class CreateSponsorHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;

    private ?RepositoryInterface $sponsorRepository;
    private ?RepositoryInterface $institutionRepository;
    private ?CreateSponsorHandler $handler;

    private ?Generator $faker;

    protected function setUp(): void
    {
        $this->handler = static::getContainer()->get(CreateSponsorHandler::class);
        $this->setUpUsers(static::getContainer());
        $this->faker = FakerFactory::create();
    }

    /**
     * @test
     */
    public function it_creates_institution_for_sponsor(): void
    {
        $dto = new CreateSponsorDto();
        $dto->name = $this->faker->company();
        $dto->privacy = Privacy::PUBLIC;
        $dto->website = 'https://'.$this->faker->domainName();

        $sponsor = $this->handler->__invoke(new CreateSponsorCommand($dto));
        self::assertNotNull($sponsor->getInstitution());
        $institution = $sponsor->getInstitution();
        self::assertEquals($dto->name, $sponsor->getName());
        self::assertEquals($dto->name, $institution->getName());
        self::assertEquals($dto->privacy, $sponsor->getPrivacy());
        self::assertEquals($dto->website, $sponsor->getWebsite());
        self::assertEquals($dto->website, $institution->getWebsite());
    }
}
