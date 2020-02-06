<?php

/*
 *  Copyright (C) 2020 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\DependencyInjection;

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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sendinblue_bridge');

        $rootNode
            ->children()
            ->scalarNode('cli_host')->defaultValue("http://localhost")->cannotBeEmpty()->end()
            ->arrayNode('sender')
            ->children()
            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('email')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->end()
            ->arrayNode('reply')
            ->children()
            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('email')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->end()
            ->arrayNode('storage')
            ->children()
            ->scalarNode('emails')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('sms')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->end()
            ->arrayNode('emails')
            ->scalarPrototype()->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->arrayNode('sms')
            ->scalarPrototype()->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
