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

use BadPixxel\BrevoBridge\Dictionary\ServiceTags;
use BadPixxel\BrevoBridge\Models;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class BrevoBridgeExtension extends Extension implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        //==============================================================================
        // Load Bundle Configuration
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $bundles = $container->getParameter('kernel.bundles');
        //==============================================================================
        // Setup App Parameters
        $container->setParameter('brevo_bridge', $config);
        $container->setParameter('brevo_bridge.user.class', $config["storage"]["user"]);
        $container->setParameter('brevo_bridge.emails.class', $config["storage"]["emails"]);
        $container->setParameter('brevo_bridge.sms.class', $config["storage"]["sms"]);
        //==============================================================================
        // Load Services
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        //==============================================================================
        // Configure Sonata Admin if Enabled
        if (!is_array($bundles) || isset($bundles['SonataAdminBundle'])) {
            $loader->load('admin.yaml');
        }
        //==============================================================================
        // Configure Autoconfiguration for Emails
        $container
            ->registerForAutoconfiguration(Models\AbstractEmail::class)
            ->addTag(ServiceTags::EMAIL)
        ;
        //==============================================================================
        // Configure Autoconfiguration for Emails Processors
        $container
            ->registerForAutoconfiguration(Models\AbstractEmailProcessor::class)
            ->addTag(ServiceTags::EMAIL_PROCESSOR)
        ;
        //==============================================================================
        // Configure Autoconfiguration for Sms
        $container
            ->registerForAutoconfiguration(Models\AbstractSms::class)
            ->addTag(ServiceTags::SMS)
        ;
        //==============================================================================
        // Configure Autoconfiguration for Events
        $container
            ->registerForAutoconfiguration(Models\AbstractTrackEvent::class)
            ->addTag(ServiceTags::EVENT)
        ;
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        //==============================================================================
        // Get Original Configuration
        /** @var array $brevoConfig */
        $brevoConfig = $container->getParameter("brevo_bridge");
        //==============================================================================
        // Walk on Configured Emails Class
        foreach (array_keys($container->findTaggedServiceIds(ServiceTags::EMAIL)) as $class) {
            $container->removeDefinition($class);
            $brevoConfig["emails"][$class] = $class;
        }
        //==============================================================================
        // Walk on Configured Sms Class
        foreach (array_keys($container->findTaggedServiceIds(ServiceTags::SMS)) as $class) {
            $container->removeDefinition($class);
            $brevoConfig["sms"][$class] = $class;
        }
        //==============================================================================
        // Walk on Configured Event Class
        foreach (array_keys($container->findTaggedServiceIds(ServiceTags::EVENT)) as $class) {
            $container->removeDefinition($class);
            $brevoConfig["event"][$class] = $class;
        }
        //==============================================================================
        // Replace Configuration
        $container->setParameter("brevo_bridge", $brevoConfig);
    }
}
