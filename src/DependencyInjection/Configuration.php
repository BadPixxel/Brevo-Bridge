<?php

/*
 *  Copyright (C) BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\BrevoBridge\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sendinblue_bridge');
        $rootNode = $treeBuilder->getRootNode();

        // @phpstan-ignore-next-line
        $rootNode
            ->children()
            ->scalarNode('api_key')->cannotBeEmpty()->end()
            ->scalarNode('track_key')->defaultValue(null)->end()
            ->scalarNode('cli_host')->defaultValue("http://localhost")->cannotBeEmpty()->end()
            ->booleanNode("disable_emails")->isRequired()->end()
            ->arrayNode('sender')
            ->isRequired()
            ->children()
            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('email')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->end()
            ->arrayNode('reply')
            ->isRequired()
            ->children()
            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('email')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->end()
            ->arrayNode('storage')
            ->children()
            ->scalarNode('user')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('emails')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('sms')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->end()
            ->arrayNode('refresh')->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('metadata')->defaultValue(true)->end()
            ->booleanNode('contents')->defaultValue(true)->end()
            ->end()
            ->end()
            ->arrayNode('emails')
            ->scalarPrototype()->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->arrayNode('sms')
            ->scalarPrototype()->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->arrayNode('events')
            ->scalarPrototype()->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->arrayNode('mjml')->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('endpoint')->defaultValue("https://api.mjml.io/v1/render")->end()
            ->scalarNode('api_key')->end()
            ->scalarNode('secret_key')->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
