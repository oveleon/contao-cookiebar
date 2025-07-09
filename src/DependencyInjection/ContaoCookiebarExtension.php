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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContaoCookiebarExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );

        $loader->load('migrations.yaml');
        $loader->load('commands.yaml');
        $loader->load('services.yaml');
        $loader->load('listener.yaml');

        $arrIframeTypes = [
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

        $arrPageTemplates = ['fe_page'];

        if (!empty($config['iframe_types']))
        {
            $config['iframe_types'] = array_merge_recursive($arrIframeTypes, $config['iframe_types']);
        }
        else
        {
            $config['iframe_types'] = $arrIframeTypes;
        }

        /**
         * @deprecated Deprecated since Contao Cookiebar 2.0, to be removed in v 2.1
         */
        if (!empty($config['page_templates']))
        {
            $config['page_templates'] = array_merge($arrPageTemplates, $config['page_templates']);
        }
        else
        {
            $config['page_templates'] = $arrPageTemplates;
        }

        $container->setParameter('contao_cookiebar.consider_dnt', $config['consider_dnt']);
        $container->setParameter('contao_cookiebar.anonymize_ip', $config['anonymize_ip']);
        $container->setParameter('contao_cookiebar.consent_log', $config['consent_log']);
        $container->setParameter('contao_cookiebar.lifetime', $config['lifetime']);
        $container->setParameter('contao_cookiebar.storage_key', $config['storage_key']);
        $container->setParameter('contao_cookiebar.iframe_types', $config['iframe_types']);
        $container->setParameter('contao_cookiebar.page_templates', $config['page_templates']);
        $container->setParameter('contao_cookiebar.disable_focustrap', $config['disable_focustrap']);
    }
}
