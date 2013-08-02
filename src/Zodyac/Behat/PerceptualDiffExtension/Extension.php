<?php

namespace Zodyac\Behat\PerceptualDiffExtension;

use Behat\Behat\Extension\ExtensionInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Extension implements ExtensionInterface
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources'));
        $loader->load('services.yml');

        $path = $config['path'];
        if (strpos($path, '/') !== 0) {
            // Determine the base path
            $path = $container->getParameter('behat.paths.base') . '/' . $path;
        }

        $container->setParameter('behat.perceptual_diff.path', $path);
        $container->setParameter('behat.perceptual_diff.sleep', $config['sleep']);
        $container->setParameter('behat.perceptual_diff.compare', $config['compare']);
    }

    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('path')->cannotBeEmpty()->end()
                ->scalarNode('sleep')->defaultValue(1)->end()
                ->arrayNode('compare')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('fuzz')->defaultValue(20)->end()
                        ->scalarNode('metric')->defaultValue('AE')->end()
                        ->scalarNode('highlight_color')->defaultValue('blue')->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    public function getCompilerPasses()
    {
        return array();
    }
}
