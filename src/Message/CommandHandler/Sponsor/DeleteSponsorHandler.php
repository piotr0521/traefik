<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Sponsor;

use Groshy\Message\Command\Sponsor\DeleteSponsorCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class DeleteSponsorHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $sponsorManager,
        private readonly ManagerInterface $institutionManager,
    ) {
    }

    public function __invoke(DeleteSponsorCommand $command): void
    {
        $sponsor = $command->sponsor;
        $institution = $sponsor->getInstitution();

        $this->sponsorManager->remove($sponsor);
        !is_null($institution) ? $this->institutionManager->remove($institution) : null;
        $this->sponsorManager->flush();
    }
}
