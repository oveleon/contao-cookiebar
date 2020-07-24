<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('contao_cookiebar');

        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // Backwards compatibility
            $rootNode = $treeBuilder->root('contao_cookiebar');
        }

        $rootNode
            ->children()
                ->booleanNode('consider_dnt')
                    ->defaultTrue()
                ->end()
                ->arrayNode('cookie')
                    ->ignoreExtraKeys()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('lifetime')
                            ->info('Cookie bar Cookie storage duration')
                            ->defaultValue(60*60*24*365)
                        ->end()
                        ->scalarNode('token')
                            ->info('Cookie bar Cookie technical name / token')
                            ->defaultValue('ccb_contao_token')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('iframe_types')
                    ->arrayPrototype()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
