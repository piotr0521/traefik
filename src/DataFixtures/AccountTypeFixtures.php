<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Entity\AccountType;
use Symfony\Component\Yaml\Yaml;
use Talav\Component\Resource\Manager\ManagerInterface;

final class AccountTypeFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly ManagerInterface $accountTypeManager,
    ) {
    }

    public function loadData(): void
    {
        $dataTypes = Yaml::parseFile(dirname(__FILE__).'/files/account_types.yaml');
        foreach ($dataTypes as $data) {
            $type = $this->createType($data);
            $this->accountTypeManager->update($type);
            if (isset($data['children'])) {
                foreach ($data['children'] as $childData) {
                    $child = $this->createType($childData);
                    $child->setParent($type);
                    $this->accountTypeManager->update($child);
                }
            }
        }
        $this->accountTypeManager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }

    private function createType(array $data): AccountType
    {
        /** @var AccountType $type */
        $type = $this->accountTypeManager->create();
        $type->setName($data['name']);
        $type->setPlaidName($data['plaidName']);
        $type->setDescription($data['description']);

        return $type;
    }
}
