<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\TagGroup;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Application\Manager\PositionNormalizer;
use Groshy\Entity\TagGroup;
use Groshy\Message\Command\TagGroup\UpdateTagGroupCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class UpdateTagGroupHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $tagGroupManager,
        private readonly PositionNormalizer $normalizer,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdateTagGroupCommand $message): TagGroup
    {
        $positionChange = is_null($message->dto->position) ? -1 : $message->dto->position - $message->tagGroup->getPosition();
        $resource = $this->mapper->mapToObject($message->dto, $message->tagGroup);
        $this->tagGroupManager->update($resource, true);
        $this->normalizer->normalize(['createdBy' => $resource->getCreatedBy()], get_class($resource), $positionChange);

        return $resource;
    }
}
