<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('groshy');
        $this->addResourceSection($treeBuilder->getRootNode());
        $this->addAssetsSection($treeBuilder->getRootNode());

        return $treeBuilder;
    }

    private function addResourceSection(ArrayNodeDefinition $node)
    {
        $resources = [
            'tag_group',
            'tag',
            'sponsor',
            'asset',
            'asset_investment',
            'asset_cash',
            'asset_property',
            'asset_certificate_deposit',
            'asset_collectable',
            'asset_security',
            'asset_crypto',
            'asset_business',
            'asset_type',
            'asset_security_price',
            'asset_crypto_price',
            'position',
            'position_investment',
            'position_cash',
            'position_property',
            'position_credit_card',
            'position_certificate_deposit',
            'position_collectable',
            'position_mortgage',
            'position_loan',
            'position_security',
            'position_crypto',
            'position_business',
            'position_value',
            'transaction',
            'liability_credit_card',
            'liability_mortgage',
            'liability_loan',
            'tag_group',
            'institution',
            'plaid_connection',
            'account',
            'account_type',
            'account_holder',
            'asset_type_account_type',
            'position_event',
            'product',
            'price',
            'customer',
            'subscription',
        ];
        $builder = $node->children()->arrayNode('resources')->addDefaultsIfNotSet()->children();
        foreach ($resources as $resName) {
            $this->addResourceNode($builder, $resName);
        }
        $builder->end()->end()->end();
    }

    private function addResourceNode(NodeBuilder $node, string $name): void
    {
        $node->arrayNode($name)
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('classes')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('model')->cannotBeEmpty()->end()
                        ->scalarNode('manager')->end()
                        ->scalarNode('repository')->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    private function addAssetsSection(ArrayNodeDefinition $node): void
    {
        $types = [
            'private_equity',
            'cash',
            'credit_card',
            'investment_property',
            'mortgage',
            'collectables',
            'peer_to_peer_lending',
            'cryptocurrency',
            'public_equity',
            'certificate_of_deposit',
            'loan',
        ];
        $builder = $node
            ->children()
                ->arrayNode('assets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('configs')
                            ->addDefaultsIfNotSet()
                            ->children();

        foreach ($types as $type) {
            $this->addAssetNode($builder, $type);
        }
        $builder
                            ->end()
                        ->end()
                        ->arrayNode('types')
                            ->normalizeKeys(false)
                            ->useAttributeAsKey('name', false)
                            ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addAssetNode(NodeBuilder $node, string $name): void
    {
        $node->arrayNode($name)
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('position_event_types')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('asset_class')->defaultNull()->end()
                ->scalarNode('position_class')->defaultNull()->end()
            ->end()
        ->end();
    }
}
