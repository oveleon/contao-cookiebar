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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class ContaoCookiebarExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('contao_cookiebar.consider_dnt', $config['consider_dnt']);
        $container->setParameter('contao_cookiebar.cookie_lifetime', $config['cookie']['lifetime']);
        $container->setParameter('contao_cookiebar.cookie_token', $config['cookie']['token']);
        $container->setParameter('contao_cookiebar.block_elements', $config['elements']);
    }
}
