<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Tag;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Application\Manager\PositionNormalizer;
use Groshy\Entity\Tag;
use Groshy\Message\Command\Tag\UpdateTagCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class UpdateTagHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $tagManager,
        private readonly PositionNormalizer $normalizer,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(UpdateTagCommand $message): Tag
    {
        $positionChange = is_null($message->dto->position) ? -1 : $message->dto->position - $message->tag->getPosition();
        $resource = $this->mapper->mapToObject($message->dto, $message->tag);
        $this->tagManager->update($resource, true);
        $this->normalizer->normalize(['tagGroup' => $resource->getTagGroup()], get_class($resource), $positionChange);

        return $resource;
    }
}
