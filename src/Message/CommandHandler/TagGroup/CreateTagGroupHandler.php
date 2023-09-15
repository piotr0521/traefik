<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\TagGroup;

use AutoMapperPlus\AutoMapperInterface;
use Groshy\Application\Manager\PositionNormalizer;
use Groshy\Entity\TagGroup;
use Groshy\Message\Command\TagGroup\CreateTagGroupCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class CreateTagGroupHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $tagGroupManager,
        private readonly PositionNormalizer $normalizer,
        private readonly AutoMapperInterface $mapper,
    ) {
    }

    public function __invoke(CreateTagGroupCommand $message): TagGroup
    {
        $resource = $this->mapper->mapToObject($message->dto, $this->tagGroupManager->create());
        $this->tagGroupManager->update($resource, true);
        $this->normalizer->normalize(['createdBy' => $resource->getCreatedBy()], get_class($resource), -1);

        return $resource;
    }
}
