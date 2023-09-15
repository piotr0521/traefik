<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Message\CommandHandler\TagGroup;

use Groshy\Message\Command\TagGroup\UpdateTagGroupCommand;
use Groshy\Message\CommandHandler\TagGroup\UpdateTagGroupHandler;
use Groshy\Message\Dto\TagGroup\UpdateTagGroupDto;
use Groshy\Tests\Helper\UsersAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Talav\Component\Resource\Repository\RepositoryInterface;

class UpdateTagGroupHandlerTest extends KernelTestCase
{
    use UsersAwareTrait;

    private ?RepositoryInterface $tagGroupRepository;
    private ?UpdateTagGroupHandler $updateHandler;

    protected function setUp(): void
    {
        $this->tagGroupRepository = static::getContainer()->get('app.repository.tag_group');
        $this->updateHandler = static::getContainer()->get(UpdateTagGroupHandler::class);
        $this->setUpUsers(static::getContainer());
    }

    /**
     * @test
     */
    public function it_updates_tag_group_name(): void
    {
        $group = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')], ['position' => 'ASC'])[0];
        $dto = new UpdateTagGroupDto();
        $dto->name = 'Updated name';
        $this->updateHandler->__invoke(new UpdateTagGroupCommand($group, $dto));
        $group = $this->tagGroupRepository->find($group->getId());
        self::assertEquals($dto->name, $group->getName());
    }

    /**
     * @test
     */
    public function it_updates_tag_group_position_to_bigger_position(): void
    {
        $group = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')], ['position' => 'ASC'])[0];
        $dto = new UpdateTagGroupDto();
        $dto->position = 1;
        $this->updateHandler->__invoke(new UpdateTagGroupCommand($group, $dto));
        $group = $this->tagGroupRepository->find($group->getId());
        self::assertEquals($dto->position, $group->getPosition());
        $groups = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')], ['position' => 'ASC']);
        foreach ($groups as $key => $group) {
            self::assertEquals($key, $group->getPosition());
        }
    }

    /**
     * @test
     */
    public function it_updates_tag_group_position_to_lower_position(): void
    {
        $group = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')], ['position' => 'ASC'])[1];
        $dto = new UpdateTagGroupDto();
        $dto->position = 0;
        $this->updateHandler->__invoke(new UpdateTagGroupCommand($group, $dto));
        $group = $this->tagGroupRepository->find($group->getId());
        self::assertEquals($dto->position, $group->getPosition());
        $groups = $this->tagGroupRepository->findBy(['createdBy' => $this->getUser('user2')], ['position' => 'ASC']);
        foreach ($groups as $key => $group) {
            self::assertEquals($key, $group->getPosition());
        }
    }
}
