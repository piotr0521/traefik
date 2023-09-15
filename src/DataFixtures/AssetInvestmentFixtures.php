<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Domain\Enum\Privacy;
use Groshy\Entity\AssetInvestment;
use Groshy\Message\Command\Sponsor\CreateSponsorCommand;
use Groshy\Message\CommandHandler\Sponsor\CreateSponsorHandler;
use Groshy\Message\Dto\Sponsor\CreateSponsorDto;
use function Sabre\Uri\normalize;
use Symfony\Component\Messenger\MessageBusInterface;
use Talav\Component\Resource\Manager\ManagerInterface;

final class AssetInvestmentFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ManagerInterface $sponsorManager,
        private readonly ManagerInterface $assetInvestmentManager,
        private readonly ManagerInterface $assetTypeManager,
        private readonly CreateSponsorHandler $handler
    ) {
    }

    public function loadData(): void
    {
        $types = $this->loadTypes();
        if (($handle = fopen(dirname(__FILE__).'/files/sponsors.csv', 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                if ('' != trim($data[0])) {
                    $dto = new CreateSponsorDto();
                    $dto->name = trim($data[0]);
                    $dto->privacy = Privacy::PUBLIC;
                    $dto->website = normalize(trim($data[1]));
                    $sponsor = $this->handler->__invoke(new CreateSponsorCommand($dto));
                }
                /** @var AssetInvestment $inv */
                $inv = $this->assetInvestmentManager->create();
                $inv->setSponsor($sponsor);
                $inv->setName(trim($data[2]));
                if ('' != trim($data[3])) {
                    $inv->getData()->setWebsite(normalize(trim($data[3])));
                }
                if ('TRUE' == trim($data[4])) {
                    $inv->getData()->setIsEvergreen(true);
                }
                if ('' != trim($data[5])) {
                    $inv->getData()->setIrr(trim($data[5]));
                }
                if ('' != trim($data[6])) {
                    $inv->getData()->setTerm(trim($data[6]));
                }
                $inv->setAssetType($types[trim($data[7])][trim($data[8])]);
                $inv->setPrivacy(Privacy::PUBLIC);
                $this->assetInvestmentManager->update($inv);
            }
            fclose($handle);
            $this->sponsorManager->flush();
        }
    }

    public function getOrder(): int
    {
        return 15;
    }

    private function loadTypes(): iterable
    {
        $cache = [];
        foreach ($this->assetTypeManager->getRepository()->findAll() as $type) {
            if (!$type->isTopLevel()) {
                $cache[$type->getParent()->getName()][$type->getName()] = $type;
            }
        }

        return $cache;
    }
}
