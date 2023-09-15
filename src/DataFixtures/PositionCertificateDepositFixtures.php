<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Enum\TransactionTypeKey;
use Groshy\Message\Command\PositionCertificateDeposit\CreatePositionCertificateDepositCommand;
use Groshy\Message\Command\Transaction\CreateTransactionCommand;
use Groshy\Message\Dto\PositionCertificateDeposit\CreatePositionCertificateDepositDto;
use Groshy\Message\Dto\Transaction\CreateTransactionDto;
use Money\Currency;
use Money\Money;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Talav\Component\Resource\Repository\RepositoryInterface;
use Talav\Component\User\Manager\UserManagerInterface;

final class PositionCertificateDepositFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly UserManagerInterface $userManager,
        private readonly RepositoryInterface $assetCertificateDepositRepository,
        private readonly RepositoryInterface $institutionRepository,
        private readonly RepositoryInterface $tagRepository,
        // private readonly RepositoryInterface $transactionTypeRepository,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function loadData(): void
    {
        return;
        $asset = $this->assetCertificateDepositRepository->getCertificateDepositAsset();
        $institutions = $this->institutionRepository->findAll();
        $users = [
            'user2' => $this->faker->numberBetween(1, 5),
            'user5' => $this->faker->numberBetween(1, 5),
            'user7' => $this->faker->numberBetween(1, 5),
            'user8' => $this->faker->numberBetween(30, 40),
        ];

        foreach ($users as $userName => $number) {
            $user = $this->userManager->getRepository()->findOneBy(['username' => $userName]);
            $tags = $this->tagRepository->findBy(['createdBy' => $user]);
            for ($i = 0; $i <= $number; ++$i) {
                $dto = new CreatePositionCertificateDepositDto();
                $dto->createdBy = $user;
                $dto->name = $this->faker->company().' CD';
                $dto->yield = $this->faker->numberBetween(45, 400) / 100 / 100;
                $dto->depositValue = new Money($this->faker->numberBetween(1, 10) * 1000 * 100, new Currency('USD'));
                $dto->asset = $asset;
                $dto->tags = $this->faker->boolean() ? $this->faker->randomElements($tags, $this->faker->numberBetween(1, 3)) : [];
                $dto->notes = $this->faker->boolean() ? $this->faker->text(200) : null;
                $dto->institution = $this->faker->boolean() ? $this->faker->randomElement($institutions) : null;

                $isCompleted = $this->faker->boolean();
                if ($isCompleted) {
                    // completed
                    $dto->depositDate = $this->faker->dateTimeBetween('-3 years', '-2 year');
                    $dto->terms = $this->faker->numberBetween(6, 12);
                } else {
                    // still active
                    $dto->depositDate = $this->faker->dateTimeBetween('-1 years', '-1 month');
                    $dto->terms = $this->faker->numberBetween(13, 36);
                }
                $position = $this->messageBus->dispatch(new CreatePositionCertificateDepositCommand($dto))->last(HandledStamp::class)->getResult();

//                if ($isCompleted) {
//                    $trDto = new CreateTransactionDto();
//                    $trDto->position = $position;
//                    $trDto->transactionDate = $dto->depositDate->modify($dto->terms.' months');
//                    $trDto->amount = $dto->depositValue->multiply(strval(1 + $dto->yield));
//                    $trDto->type = $this->transactionTypeRepository->findOneBy(['shortName' => TransactionTypeKey::VALUE_UPDATE]);
//                    $trDto->isCompleted = true;
//                    $this->messageBus->dispatch(new CreateTransactionCommand($trDto));
//                }
            }
        }
    }

    public function getOrder(): int
    {
        return 100;
    }
}
