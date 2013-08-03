<?php

namespace Zodyac\Behat\PerceptualDiffExtension;

use Behat\Behat\Extension\ExtensionInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

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

        $container->setParameter('behat.perceptual_diff_extension.path', $path);
        $container->setParameter('behat.perceptual_diff_extension.sleep', $config['sleep']);
        $container->setParameter('behat.perceptual_diff_extension.compare', $config['compare']);
        $container->setParameter('behat.perceptual_diff_extension.fail_on_diff', $config['fail_on_diff']);

        // Override the standard HTML formatter with a more extensible version
        $formatterClass = 'Zodyac\Behat\ExtensibleHtmlFormatter\Formatter\ExtensibleHtmlFormatter';
        $formatterDispatcherClass = 'Zodyac\Behat\ExtensibleHtmlFormatter\Formatter\ExtensibleHtmlFormatterDispatcher';
        $formatterDispatcherId = 'behat.extensible_html_formatter.formatter.dispatcher.html';
        if (class_exists($formatterClass) && !$container->hasDefinition($formatterDispatcherId)) {
            $htmlFormatterDefinition = new Definition($formatterDispatcherClass, array(
                $formatterClass,
                'html',
                'Generates a nice looking HTML report.',
                new Reference('behat.event_dispatcher')
            ));

            $htmlFormatterDefinition->addTag('behat.formatter.dispatcher');

            $container->setDefinition($formatterDispatcherId, $htmlFormatterDefinition);
        }
    }

    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('path')->cannotBeEmpty()->end()
                ->scalarNode('sleep')->defaultValue(1)->end()
                ->booleanNode('fail_on_diff')->defaultValue(true)->end()
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
