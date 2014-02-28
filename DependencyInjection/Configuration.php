<?php

namespace Vivait\AuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vivait_auth', 'array');

        $rootNode
          ->fixXmlConfig('templates')
          ->arrayNode('templates')
            ->addDefaultsIfNotSet()
              ->children()
                ->scalarNode('base')->defaultValue('VivaitAuthBundle:Templates:base.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('changepassword')->defaultValue('VivaitAuthBundle:Form:changepassword.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('changetenants')->defaultValue('VivaitAuthBundle:Form:changetenants.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('groups')->defaultValue('VivaitAuthBundle:Default:groups.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('heartbeat')->defaultValue('VivaitAuthBundle:Partials:heartbeat.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('impersonateuser')->defaultValue('VivaitAuthBundle:Form:impersonateuser.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('login')->defaultValue('VivaitAuthBundle:Default:login.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('menu')->defaultValue('VivaitAuthBundle:Partials:menu.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('statusbadge')->defaultValue('VivaitAuthBundle:Partials:statusbadge.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('tenants')->defaultValue('VivaitAuthBundle:Default:tenants.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('usermenu')->defaultValue('VivaitAuthBundle:Partials:usermenu.html.twig')->cannotBeEmpty()->end()
                ->scalarNode('users')->defaultValue('VivaitAuthBundle:Default:users.html.twig')->cannotBeEmpty()->end()
              ->end()
            ->end();


        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
