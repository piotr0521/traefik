<?php

declare(strict_types=1);

namespace Groshy\Message\CommandHandler\Tag;

use Doctrine\Persistence\ManagerRegistry;
use Groshy\Entity\Tag;
use Groshy\Entity\TagGroup;
use Groshy\Message\Command\Tag\ResetTagsCommand;
use Groshy\Provider\DefaultTagProvider;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\User\Manager\UserManagerInterface;
use Talav\Component\User\Message\Event\NewUserEvent;
use Talav\Component\User\Model\UserInterface;
use Webmozart\Assert\Assert;

final class ResetTagsHandler implements MessageHandlerInterface, MessageSubscriberInterface
{
    public function __construct(
        private readonly ManagerInterface $tagGroupManager,
        private readonly ManagerInterface $tagManager,
        private readonly UserManagerInterface $userManager,
        private readonly DefaultTagProvider $provider,
        // @todo, hack, need to find an actual reason why entity has not been properly removed
        private readonly ManagerRegistry $registry,
    ) {
    }

    public static function getHandledMessages(): iterable
    {
        // handle this message on __invoke
        yield ResetTagsCommand::class;
        yield NewUserEvent::class => [
            'method' => 'handleNewUser',
        ];
    }

    public function __invoke(ResetTagsCommand $message): void
    {
        $this->resetTags($message->user);
    }

    public function handleNewUser(NewUserEvent $event): void
    {
        $user = $this->userManager->getRepository()->find($event->id);
        Assert::notNull($user);
        $this->addInitialTags($user);
    }

    private function addInitialTags(UserInterface $user): void
    {
        foreach ($this->provider->getTagsStructure() as $struct) {
            /** @var TagGroup $tagGroup */
            $tagGroup = $this->tagGroupManager->create();
            $tagGroup->setName($struct['name']);
            $tagGroup->setPosition($struct['position']);
            $tagGroup->setCreatedBy($user);
            $this->tagGroupManager->update($tagGroup);
            foreach ($struct['tags'] as $tagStruct) {
                /** @var Tag $tag */
                $tag = $this->tagManager->create();
                $tag->setName($tagStruct['name']);
                $tag->setPosition($tagStruct['position']);
                $tag->setColor($tagStruct['color']);
                $tag->setTagGroup($tagGroup);
                $tag->setCreatedBy($user);
                $this->tagManager->update($tag);
            }
        }
    }

    private function resetTags(UserInterface $user): void
    {
        $groups = [];
        $tags = [];
        foreach ($this->provider->getTagsStructure() as $struct) {
            $groups[$struct['name']] = 1;
            foreach ($struct['tags'] as $tagStruct) {
                $tags[$struct['name']][$tagStruct['name']] = 1;
            }
        }

        foreach ($this->tagGroupManager->getRepository()->findBy(['createdBy' => $user]) as $tagGroup) {
            if (!isset($groups[$tagGroup->getName()])) {
                $this->tagGroupManager->remove($tagGroup);
                continue;
            }
            foreach ($tagGroup->getTags() as $tag) {
                if (!isset($tags[$tagGroup->getName()][$tag->getName()])) {
                    $this->tagManager->remove($tag);
                }
            }
        }
        $this->registry->getManagerForClass($this->tagGroupManager->getClassName())->clear($this->tagGroupManager->getClassName());
        $this->registry->getManagerForClass($this->tagManager->getClassName())->clear($this->tagManager->getClassName());

        foreach ($this->provider->getTagsStructure() as $struct) {
            /** @var TagGroup $tagGroup */
            $tagGroup = $this->tagGroupManager->getRepository()->findOneBy(['createdBy' => $user, 'name' => $struct['name']]);
            if (is_null($tagGroup)) {
                $tagGroup = $this->tagGroupManager->create();
                $tagGroup->setName($struct['name']);
                $tagGroup->setPosition($struct['position']);
                $tagGroup->setCreatedBy($user);
            } else {
                $tagGroup->setPosition($struct['position']);
            }
            $this->tagGroupManager->update($tagGroup);
            foreach ($struct['tags'] as $tagStruct) {
                /** @var Tag $tag */
                $tag = $this->tagManager->getRepository()->findOneBy(['tagGroup' => $tagGroup, 'name' => $tagStruct['name']]);
                if (is_null($tag)) {
                    $tag = $this->tagManager->create();
                    $tag->setName($tagStruct['name']);
                    $tag->setPosition($tagStruct['position']);
                    $tag->setColor($tagStruct['color']);
                    $tag->setTagGroup($tagGroup);
                    $tag->setCreatedBy($user);
                } else {
                    $tag->setPosition($tagStruct['position']);
                }
                $this->tagManager->update($tag);
            }
        }
        $this->tagManager->flush();
    }
}
