<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Entity\Institution;
use Symfony\Component\Yaml\Yaml;
use Talav\Component\Resource\Manager\ManagerInterface;

final class InstitutionFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly ManagerInterface $institutionManager,
    ) {
    }

    public function loadData(): void
    {
        return;
        $plaidInsFile = dirname(__FILE__).'/files/institutions.yaml';
        $new = [];
        if (($handle = fopen(dirname(__FILE__).'/files/ins.csv', 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $name = trim($data[0]);
                $new[$name] = [$name, trim($data[1])];
            }
        }

        $ids = [];
        foreach (Yaml::parse(file_get_contents($plaidInsFile)) as $el) {
            if (isset($ids[$el['plaidId']])) {
                continue;
            }
            /** @var Institution $ins */
            $ins = $this->institutionManager->create();
            $ins->setName($el['name']);
            $ins->setWebsite($el['url']);
            $ins->setPlaidId($el['plaidId']);
            $this->institutionManager->update($ins);
            if (isset($new[$el['name']])) {
                unset($new[$el['name']]);
            }
            $ids[$el['plaidId']] = 1;
        }
        foreach ($new as $el) {
            /** @var Institution $ins */
            $ins = $this->institutionManager->create();
            $ins->setName($el[0]);
            $ins->setWebsite($el[1]);
            $this->institutionManager->update($ins);
        }

        $this->institutionManager->flush();
    }

    public function getOrder(): int
    {
        return 5;
    }
}
