<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Position;

use Groshy\Entity\Position;
use Groshy\Message\Command\PositionBusiness\DeletePositionBusinessCommand;
use Groshy\Message\Command\PositionCash\DeletePositionCashCommand;
use Groshy\Message\Command\PositionCertificateDeposit\DeletePositionCertificateDepositCommand;
use Groshy\Message\Command\PositionCollectable\DeletePositionCollectableCommand;
use Groshy\Message\Command\PositionCreditCard\DeletePositionCreditCardCommand;
use Groshy\Message\Command\PositionCrypto\DeletePositionCryptoCommand;
use Groshy\Message\Command\PositionInvestment\DeletePositionInvestmentCommand;
use Groshy\Message\Command\PositionLoan\DeletePositionLoanCommand;
use Groshy\Message\Command\PositionMortgage\DeletePositionMortgageCommand;
use Groshy\Message\Command\PositionProperty\DeletePositionPropertyCommand;
use Groshy\Message\Command\PositionSecurity\DeletePositionSecurityCommand;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Talav\Component\Registry\Registry\ServiceRegistryInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class DeletePositionHandler implements MessageSubscriberInterface
{
    public function __construct(
        private readonly RepositoryInterface $transactionRepository,
        private readonly RepositoryInterface $positionValueRepository,
        private readonly RepositoryInterface $positionEventRepository,
        private readonly ServiceRegistryInterface $managerRegistry,
    ) {
    }

    public static function getHandledMessages(): iterable
    {
        yield DeletePositionInvestmentCommand::class;
        yield DeletePositionCashCommand::class;
        yield DeletePositionCreditCardCommand::class;
        yield DeletePositionPropertyCommand::class;
        yield DeletePositionCertificateDepositCommand::class;
        yield DeletePositionCollectableCommand::class;
        yield DeletePositionMortgageCommand::class;
        yield DeletePositionLoanCommand::class;
        yield DeletePositionSecurityCommand::class;
        yield DeletePositionCryptoCommand::class;
        yield DeletePositionBusinessCommand::class;
    }

    public function __invoke(mixed $message): void
    {
        $manager = $this->managerRegistry->get(get_class($message->position));
        /** @var Position $position */
        $position = $message->position;
        $asset = $position->getAsset();
        $position->removeLastValue();
        $manager->flush();
        $this->transactionRepository->deleteByPosition($position);
        $this->positionValueRepository->deleteByPosition($position);
        $this->positionEventRepository->deleteByPosition($position);
        $manager->remove($position);
        $manager->flush();

        // hack for now, looks like property and business are the only two cases where this functionality is required
        if ($message instanceof DeletePositionPropertyCommand || $message instanceof DeletePositionBusinessCommand) {
            $assetManager = $this->managerRegistry->get(get_class($asset));
            $assetManager->remove($asset);
            $manager->flush();
        }
    }
}
