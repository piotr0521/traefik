<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Entity\AssetCash;
use Groshy\Entity\AssetCertificateDeposit;
use Groshy\Entity\AssetCollectable;
use Groshy\Entity\LiabilityCreditCard;
use Groshy\Entity\LiabilityLoan;
use Groshy\Entity\LiabilityMortgage;
use Talav\Component\Resource\Manager\ManagerInterface;

final class AssetSinglePerTypeFixtures extends BaseFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly ManagerInterface $liabilityCreditCardManager,
        private readonly ManagerInterface $liabilityMortgageManager,
        private readonly ManagerInterface $liabilityLoanManager,
        private readonly ManagerInterface $assetCashManager,
        private readonly ManagerInterface $assetCertificateDepositManager,
        private readonly ManagerInterface $assetCollectableManager,
        private readonly ManagerInterface $assetTypeManager
    ) {
    }

    public function loadData(): void
    {
        /** @var LiabilityCreditCard $liability */
        $liability = $this->liabilityCreditCardManager->create();
        $liability->setName('Credit Card');
        $liability->setAssetType($this->assetTypeManager->getRepository()->findOneBy(['name' => 'Credit Card']));
        $this->liabilityCreditCardManager->update($liability);

        /** @var LiabilityMortgage $liability */
        $liability = $this->liabilityMortgageManager->create();
        $liability->setName('Mortgage');
        $liability->setAssetType($this->assetTypeManager->getRepository()->findOneBy(['name' => 'Mortgage']));
        $this->liabilityMortgageManager->update($liability);

        /** @var LiabilityLoan $liability */
        $liability = $this->liabilityLoanManager->create();
        $liability->setName('Loan');
        $liability->setAssetType($this->assetTypeManager->getRepository()->findOneBy(['name' => 'Loan']));
        $this->liabilityLoanManager->update($liability);

        /** @var AssetCash $asset */
        $asset = $this->assetCashManager->create();
        $asset->setName('Cash');
        $asset->setAssetType($this->assetTypeManager->getRepository()->findOneBy(['name' => 'Cash']));
        $this->assetCashManager->update($asset, true);

        /** @var AssetCertificateDeposit $asset */
        $asset = $this->assetCertificateDepositManager->create();
        $asset->setName('Certificate of Deposit');
        $asset->setAssetType($this->assetTypeManager->getRepository()->findOneBy(['name' => 'Certificate of Deposit']));
        $this->assetCertificateDepositManager->update($asset, true);

        /** @var AssetCollectable $asset */
        $asset = $this->assetCollectableManager->create();
        $asset->setName('Collectables');
        $asset->setAssetType($this->assetTypeManager->getRepository()->findOneBy(['name' => 'Collectables']));
        $this->assetCollectableManager->update($asset, true);
    }

    public function getOrder(): int
    {
        return 10;
    }
}
