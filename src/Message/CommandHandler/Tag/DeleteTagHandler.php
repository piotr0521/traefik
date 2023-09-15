<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Tag;

use Groshy\Application\Manager\PositionNormalizer;
use Groshy\Message\Command\Tag\DeleteTagCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class DeleteTagHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $tagManager,
        private readonly PositionNormalizer $normalizer,
    ) {
    }

    public function __invoke(DeleteTagCommand $message): void
    {
        $group = $message->tag->getTagGroup();
        $this->tagManager->remove($message->tag);
        $this->tagManager->flush();
        $this->normalizer->normalize(['tagGroup' => $group], get_class($message->tag), -1);
    }
}
