<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

declare(strict_types=1);

namespace Oveleon\ContaoCookiebar;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ContaoCookiebar extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
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
                    ->setDeprecated('oveleon/contao-cookiebar', '2.0', 'Using page_templates is deprecated. Consider removing it.')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('iframe_types')
                    ->arrayPrototype()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->booleanNode('disable_focustrap')
                    ->defaultFalse()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/commands.yaml');
        $container->import('../config/listener.yaml');
        $container->import('../config/migrations.yaml');
        $container->import('../config/services.yaml');

        $this->setDefaultConfiguration($config);

        $builder->setParameter('contao_cookiebar.consider_dnt', $config['consider_dnt']);
        $builder->setParameter('contao_cookiebar.anonymize_ip', $config['anonymize_ip']);
        $builder->setParameter('contao_cookiebar.consent_log', $config['consent_log']);
        $builder->setParameter('contao_cookiebar.lifetime', $config['lifetime']);
        $builder->setParameter('contao_cookiebar.storage_key', $config['storage_key']);
        $builder->setParameter('contao_cookiebar.iframe_types', $config['iframe_types']);
        $builder->setParameter('contao_cookiebar.page_templates', $config['page_templates']);
        $builder->setParameter('contao_cookiebar.disable_focustrap', $config['disable_focustrap']);

    }

    private function setDefaultConfiguration(array &$config): void
    {
        $inlineFrameTypes = [
            'youtube' => [
                'ce_youtube',
                'youtube'
            ],
            'vimeo' => [
                'ce_vimeo',
                'vimeo'
            ],
            'googlemaps' => [
                'ce_html_googlemaps',
                'mod_html_googlemaps',
                'content_element/html/googlemaps',
                'content_element/unfiltered_html/googlemaps',
                'frontend_module/unfiltered_html/googlemaps'
            ],
            'openstreetmap' => [
                'ce_html_openstreetmap',
                'mod_html_openstreetmap',
                'content_element/html/openstreetmap',
                'content_element/unfiltered_html/openstreetmap',
                'frontend_module/unfiltered_html/openstreetmap'
            ],
        ];

        $config['iframe_types'] = array_merge_recursive($inlineFrameTypes, $config['iframe_types'] ?? []);

        /**
         * @deprecated Deprecated since v2.0, to be removed in v3.0
         */
        $config['page_templates'] = array_merge(['fe_page'], $config['page_templates'] ?? []);
    }
}
