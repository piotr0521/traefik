<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\TagGroup;

use Groshy\Application\Manager\PositionNormalizer;
use Groshy\Message\Command\TagGroup\DeleteTagGroupCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class DeleteTagGroupHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ManagerInterface $tagGroupManager,
        private readonly PositionNormalizer $normalizer,
    ) {
    }

    public function __invoke(DeleteTagGroupCommand $message): void
    {
        $createdBy = $message->tagGroup->getCreatedBy();
        $this->tagGroupManager->remove($message->tagGroup);
        $this->tagGroupManager->flush();
        $this->normalizer->normalize(['createdBy' => $createdBy], get_class($message->tagGroup), -1);
    }
}
