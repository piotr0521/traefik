<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Entity\User;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Talav\Component\User\Manager\UserManagerInterface;
use Talav\Component\User\Message\Command\CreateUserCommand;
use Talav\Component\User\Message\Dto\CreateUserDto;

final class UserFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly UserManagerInterface $userManager
    ) {
    }

    public function loadData(): void
    {
        for ($i = 0; $i < 25; ++$i) {
            $dto = new CreateUserDto();
            $dto->username = 'user'.$i;
            $dto->email = 'user'.$i.'@test.com';
            $dto->password = 'user'.$i;
            $dto->active = true;
            $dto->firstName = $this->faker->firstName;
            $dto->lastName = $this->faker->lastName;

            /** @var User $user */
            $user = $this->messageBus->dispatch(new CreateUserCommand($dto))->last(HandledStamp::class)->getResult();
            if ($i <= 20) {
                $user->addRole(User::ROLE_CUSTOMER);
            }
        }
        $this->userManager->flush();
    }

    public function getOrder(): int
    {
        return 10;
    }
}
