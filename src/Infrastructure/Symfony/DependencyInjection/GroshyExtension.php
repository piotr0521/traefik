<?php

declare(strict_types=1);

namespace Groshy\Infrastructure\Symfony\DependencyInjection;

use Groshy\Config\ConfigModel;
use Groshy\Config\ConfigProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Talav\Component\Registry\Registry\ServiceRegistry;
use Talav\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;

class GroshyExtension extends AbstractResourceExtension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // Load services.
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../../../config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $this->registerResources('app', $config['resources'], $container);

        $container->setDefinition(ConfigProvider::class, new Definition(ConfigProvider::class, [
            $this->processTypesConfig($config['assets']['configs']),
            $config['assets']['types'],
        ]));
    }

    private function processTypesConfig(array $configs): Definition
    {
        $registry = new Definition(ServiceRegistry::class, [ConfigModel::class, 'config']);
        foreach ($configs as $key => $config) {
            $configModel = new Definition(ConfigModel::class);
            $configModel->setProperty('assetClass', $config['asset_class']);
            $configModel->setProperty('positionClass', $config['position_class']);
            $configModel->setProperty('positionEventTypes', $config['position_event_types']);
            $registry->addMethodCall('register', [$key, $configModel]);
        }

        return $registry;
    }
}
