<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Tag;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Application\Manager\PositionNormalizer;
use Groshy\Entity\Tag;
use Groshy\Message\Command\Tag\CreateTagCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class CreateTagHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $tagManager,
        private readonly PositionNormalizer $normalizer,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(CreateTagCommand $message): Tag
    {
        $resource = $this->mapper->mapToObject($message->dto, $this->tagManager->create());
        $this->tagManager->update($resource, true);
        $this->normalizer->normalize(['tagGroup' => $resource->getTagGroup()], get_class($resource), -1);

        return $resource;
    }
}
