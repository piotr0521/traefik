<?php

declare(strict_types=1);

namespace Groshy\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Groshy\Entity\AccountType;
use Groshy\Entity\AssetType;
use Groshy\Entity\AssetTypeAccountType;
use Talav\Component\Resource\Manager\ManagerInterface;
use Talav\Component\Resource\Repository\RepositoryInterface;

final class AssetTypeFixtures extends BaseFixture implements OrderedFixtureInterface
{
    private array $accountTypes = [];

    public function __construct(
        private readonly ManagerInterface $assetTypeManager,
        private readonly ManagerInterface $assetTypeAccountTypeManager,
        private readonly RepositoryInterface $accountTypeRepository,
    ) {
    }

    public function loadData(): void
    {
        foreach ($this->getData() as $data) {
            $type = $this->createType($data);
            $this->assetTypeManager->update($type);
            if (isset($data['children'])) {
                foreach ($data['children'] as $childData) {
                    $child = $this->createType($childData);
                    $child->setParent($type);
                    $this->assetTypeManager->update($child);
                }
            }
        }
        $this->assetTypeManager->flush();
    }

    public function getOrder(): int
    {
        return 2;
    }

    private function createType(array $data): AssetType
    {
        /** @var AssetType $type */
        $type = $this->assetTypeManager->create();
        $type->setName($data['name']);
        $type->setPosition($data['position']);
        $type->setIsAsset($data['is_asset']);
        $type->setIsActive($data['is_active']);
        if (isset($data['is_quantity'])) {
            $type->setIsQuantity($data['is_quantity']);
        }
        if (isset($data['account_types'])) {
            foreach ($data['account_types'] as $typeString) {
                /** @var AssetTypeAccountType $map */
                $map = $this->assetTypeAccountTypeManager->create();
                $map->setAccountType($this->findAccountType($typeString));
                $map->setAssetType($type);
                $map->setIsSubtype(false);
                $this->assetTypeAccountTypeManager->update($map);
            }
        }

        return $type;
    }

    /**
     * @return array<AccountType>
     */
    private function getAccountTypes(): array
    {
        if (0 == count($this->accountTypes)) {
            $this->accountTypes = $this->accountTypeRepository->findAll();
        }

        return $this->accountTypes;
    }

    private function findAccountType($name): AccountType
    {
        foreach ($this->getAccountTypes() as $accountType) {
            if ($accountType->getName() == $name) {
                return $accountType;
            }
        }
        throw new \RuntimeException("Account $name is not found");
    }

    private function getData(): array
    {
        return [
            [
                'name' => 'Real Estate',
                'position' => 0,
                'is_asset' => true,
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'Hard Money Loan Fund',
                        'position' => 1,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Public Non Traded REIT',
                        'position' => 2,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Real Estate GP Fund',
                        'position' => 3,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Real Estate LP Fund',
                        'position' => 4,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'name' => 'Private Equity',
                'position' => 6,
                'is_asset' => true,
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'Private Equity GP Fund',
                        'position' => 7,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Private Equity LP Fund',
                        'position' => 8,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Venture Capital',
                        'position' => 9,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Search Fund',
                        'position' => 10,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Secondaries',
                        'position' => 11,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'name' => 'Alternative Investment',
                'position' => 12,
                'is_asset' => true,
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'Litigation Financing',
                        'position' => 13,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Music Royalties',
                        'position' => 14,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Life Insurance Settlements',
                        'position' => 15,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'Private Credit',
                        'position' => 16,
                        'is_asset' => true,
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'name' => 'Cash Equivalent',
                'position' => 17,
                'is_asset' => true,
                'is_active' => true,
                'children' => [
                    [
                        'name' => 'Cash',
                        'position' => 18,
                        'is_asset' => true,
                        'is_active' => true,
                        'account_types' => [
                            'Checking',
                            'Savings',
                            'Money Market',
                            'Paypal Cash',
                            'Prepaid Debit Card',
                            'Cash Management',
                            'EBT',
                        ],
                    ],
                    [
                        'name' => 'Certificate of Deposit',
                        'position' => 19,
                        'is_asset' => true,
                        'is_active' => false,
                        'account_types' => [
                            'CD',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Public Equity',
                'position' => 20,
                'is_asset' => true,
                'is_active' => false,
                'is_quantity' => true,
            ],
            [
                'name' => 'Cryptocurrency',
                'position' => 21,
                'is_asset' => true,
                'is_active' => false,
                'is_quantity' => true,
            ],
            [
                'name' => 'Investment Property',
                'position' => 22,
                'is_asset' => true,
                'is_active' => false,
            ],
            [
                'name' => 'Private Business',
                'position' => 23,
                'is_asset' => true,
                'is_active' => false,
            ],
            [
                'name' => 'Collectables',
                'position' => 24,
                'is_asset' => true,
                'is_active' => false,
            ],
            [
                'name' => 'Credit Card',
                'position' => 25,
                'is_asset' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Mortgage',
                'position' => 26,
                'is_asset' => false,
                'is_active' => false,
            ],
            [
                'name' => 'Loan',
                'position' => 27,
                'is_asset' => false,
                'is_active' => false,
            ],
        ];
    }
}
