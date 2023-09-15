<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\Tag;

use Groshy\Domain\Enum\Color;
use Groshy\Entity\Tag;
use Groshy\Entity\TagGroup;
use Groshy\Message\Command\Tag\CreateTagCommand;
use Groshy\Message\Command\Tag\ResetTagsCommand;
use Groshy\Message\Command\TagGroup\CreateTagGroupCommand;
use Groshy\Message\Command\TagGroup\DeleteTagGroupCommand;
use Groshy\Message\CommandHandler\Tag\CreateTagHandler;
use Groshy\Message\CommandHandler\Tag\ResetTagsHandler;
use Groshy\Message\CommandHandler\TagGroup\CreateTagGroupHandler;
use Groshy\Message\CommandHandler\TagGroup\DeleteTagGroupHandler;
use Groshy\Message\Dto\Tag\CreateTagDto;
use Groshy\Message\Dto\TagGroup\CreateTagGroupDto;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class ResetTagsHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;

    private ?RepositoryInterface $tagGroupRepository;
    private ?RepositoryInterface $tagRepository;
    private ?CreateTagGroupHandler $createTagGroupHandler;
    private ?DeleteTagGroupHandler $deleteTagGroupHandler;
    private ?CreateTagHandler $createTagHandler;
    private ?ResetTagsHandler $resetTagsHandler;

    protected function setUp(): void
    {
        $this->tagGroupRepository = static::getContainer()->get('app.repository.tag_group');
        $this->tagRepository = static::getContainer()->get('app.repository.tag');
        $this->createTagGroupHandler = static::getContainer()->get(CreateTagGroupHandler::class);
        $this->deleteTagGroupHandler = static::getContainer()->get(DeleteTagGroupHandler::class);
        $this->createTagHandler = static::getContainer()->get(CreateTagHandler::class);
        $this->resetTagsHandler = static::getContainer()->get(ResetTagsHandler::class);
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_deletes_newly_added_test_group_without_any_tags(): void
    {
        $this->createNewTagGroup('New tag group');
        self::assertCount(3, $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')]));
        $this->resetTagsHandler->__invoke(new ResetTagsCommand($this->getUser('user2')));
        self::assertCount(2, $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')]));
    }

    /**
     * @test
     */
    public function it_deletes_newly_added_test_group_with_tags(): void
    {
        $groupsBefore = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')]);
        $tagsBefore = $this->tagRepository->findBy(['createdBy' => $this->getUser('user2')]);
        $group = $this->createNewTagGroup('New tag group');
        $this->createNewTag('New tag', $group);
        self::assertCount(count($groupsBefore) + 1, $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')]));
        self::assertCount(count($tagsBefore) + 1, $this->tagRepository->findBy(['createdBy' => $this->getUser('user2')]));
        $this->resetTagsHandler->__invoke(new ResetTagsCommand($this->getUser('user2')));
        self::assertCount(count($groupsBefore), $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')]));
        self::assertCount(count($tagsBefore), $this->tagRepository->findBy(['createdBy' => $this->getUser('user2')]));
    }

    /**
     * @test
     */
    public function it_restores_all_tag_groups(): void
    {
        $groupsBefore = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')]);
        $tagsBefore = $this->tagRepository->findBy(['createdBy' => $this->getUser('user2')]);
        foreach ($groupsBefore as $group) {
            $this->deleteTagGroupHandler->__invoke(new DeleteTagGroupCommand($group));
        }
        self::assertCount(0, $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')]));
        self::assertCount(0, $this->tagRepository->findBy(['createdBy' => $this->getUser('user2')]));
        $this->resetTagsHandler->__invoke(new ResetTagsCommand($this->getUser('user2')));
        self::assertCount(count($groupsBefore), $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')]));
        self::assertCount(count($tagsBefore), $this->tagRepository->findBy(['createdBy' => $this->getUser('user2')]));
    }

    private function createNewTagGroup(string $name): TagGroup
    {
        $dto = new CreateTagGroupDto();
        $dto->name = $name;
        $dto->position = 0;
        $dto->createdBy = $this->getUser('user2');

        return $this->createTagGroupHandler->__invoke(new CreateTagGroupCommand($dto));
    }

    private function createNewTag(string $name, TagGroup $tagGroup): Tag
    {
        $dto = new CreateTagDto();
        $dto->name = $name;
        $dto->position = 0;
        $dto->color = Color::COLOR1;
        $dto->tagGroup = $tagGroup;
        $dto->createdBy = $this->getUser('user2');

        return $this->createTagHandler->__invoke(new CreateTagCommand($dto));
    }
}
