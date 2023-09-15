<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\TagGroup;

use Groshy\Message\Command\TagGroup\CreateTagGroupCommand;
use Groshy\Message\CommandHandler\TagGroup\CreateTagGroupHandler;
use Groshy\Message\Dto\TagGroup\CreateTagGroupDto;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class CreateTagGroupHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;

    private ?RepositoryInterface $tagGroupRepository;
    private ?CreateTagGroupHandler $createHandler;

    protected function setUp(): void
    {
        $this->tagGroupRepository = static::getContainer()->get('app.repository.tag_group');
        $this->createHandler = static::getContainer()->get(CreateTagGroupHandler::class);
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_updates_position_for_all_tag_groups_puts_position_on_the_first_place_if_position_zero(): void
    {
        $groups = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')], ['position' => 'ASC']);
        foreach ($groups as $key => $group) {
            self::assertEquals($key, $group->getPosition());
        }
        self::assertCount(2, $groups);
        $dto = new CreateTagGroupDto();
        $dto->name = 'New name';
        $dto->position = 0;
        $dto->createdBy = $this->getUser('user2');
        $this->createHandler->__invoke(new CreateTagGroupCommand($dto));
        $groups = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')], ['position' => 'ASC']);
        foreach ($groups as $key => $group) {
            self::assertEquals($key, $group->getPosition());
        }
        self::assertCount(3, $groups);
        self::assertEquals($dto->name, $groups[0]->getName());
    }

    /**
     * @test
     */
    public function it_updates_position_for_all_tag_groups_puts_position_on_the_last_place_if_position_is_high(): void
    {
        $groups = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')], ['position' => 'ASC']);
        foreach ($groups as $key => $group) {
            self::assertEquals($key, $group->getPosition());
        }
        self::assertCount(2, $groups);
        $dto = new CreateTagGroupDto();
        $dto->name = 'New name';
        $dto->position = 9999;
        $dto->createdBy = $this->getUser('user2');
        $this->createHandler->__invoke(new CreateTagGroupCommand($dto));
        $groups = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')], ['position' => 'ASC']);
        foreach ($groups as $key => $group) {
            self::assertEquals($key, $group->getPosition());
        }
        self::assertCount(3, $groups);
        self::assertEquals($dto->name, $groups[2]->getName());
    }
}
