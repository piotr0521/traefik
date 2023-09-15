<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Sponsor;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\Institution;
use Groshy\Entity\Sponsor;
use Groshy\Message\Command\Sponsor\CreateSponsorCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class CreateSponsorHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $sponsorManager,
        private readonly ManagerInterface $institutionManager,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(CreateSponsorCommand $message): Sponsor
    {
        /** @var Sponsor $sponsor */
        $sponsor = $this->mapper->mapToObject($message->dto, $this->sponsorManager->create());
        if ($sponsor->isPublic()) {
            /** @var Institution $institution */
            $institution = $this->institutionManager->create();
            $institution->setCreatedBy($sponsor->getCreatedBy());
            $institution->setWebsite($sponsor->getWebsite());
            $institution->setName($sponsor->getName());
            $sponsor->setInstitution($institution);
            $this->institutionManager->update($institution);
        }
        $this->sponsorManager->update($sponsor, true);

        return $sponsor;
    }
}
