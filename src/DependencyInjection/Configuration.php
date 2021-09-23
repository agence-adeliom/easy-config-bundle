<?php

namespace Adeliom\EasyConfigBundle\DependencyInjection;

use Adeliom\EasyConfigBundle\Entity\Config;
use Adeliom\EasyConfigBundle\Repository\ConfigRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;


/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('easy_config');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('config_class')
                    ->isRequired()
                    ->validate()
                        ->ifString()
                        ->then(function($value) {
                            if (!class_exists($value) || !is_a($value, Config::class, true)) {
                                throw new InvalidConfigurationException(sprintf(
                                    'Config class must be a valid class extending %s. "%s" given.',
                                    Config::class, $value
                                ));
                            }
                            return $value;
                        })
                    ->end()
                ->end()
                ->scalarNode('config_repository')
                    ->defaultValue(ConfigRepository::class)
                    ->validate()
                        ->ifString()
                        ->then(function($value) {
                            if (!class_exists($value) || !is_a($value, ConfigRepository::class, true)) {
                                throw new InvalidConfigurationException(sprintf(
                                    'Config repository must be a valid class extending %s. "%s" given.',
                                    ConfigRepository::class, $value
                                ));
                            }
                            return $value;
                        })
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
