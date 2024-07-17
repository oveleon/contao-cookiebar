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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('contao_cookiebar');
        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('consider_dnt')
                    ->defaultFalse()
                ->end()
                ->booleanNode('anonymize_ip')
                    ->defaultFalse()
                ->end()
                ->booleanNode('consent_log')
                    ->defaultFalse()
                ->end()
                ->integerNode('lifetime')
                    ->info('Lifetime in seconds (default = 2 years = 63072000)')
                    ->defaultValue(63072000)
                ->end()
                ->scalarNode('storage_key')
                    ->info('localStorage key')
                    ->defaultValue('ccb_contao_token')
                ->end()
                ->arrayNode('page_templates')
                    ->prototype('scalar')->end()
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
