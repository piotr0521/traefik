<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Sponsor;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Entity\Sponsor;
use Groshy\Message\Command\Sponsor\UpdateSponsorCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Webmozart\Assert\Assert;

final class UpdateSponsorHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $sponsorManager,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdateSponsorCommand $command): Sponsor
    {
        $this->validateCommand($command);
        $sponsor = $command->sponsor;
        $dto = $command->dto;

        /** @var Sponsor $sponsor */
        $sponsor = $this->mapper->mapToObject($dto, $sponsor);
        $this->sponsorManager->update($sponsor, true);

        return $sponsor;
    }

    private function validateCommand(UpdateSponsorCommand $command)
    {
        $sponsor = $command->sponsor;
        $dto = $command->dto;
        Assert::true($sponsor->isPublic() || $sponsor->getPrivacy() == $dto->privacy);
    }
}
