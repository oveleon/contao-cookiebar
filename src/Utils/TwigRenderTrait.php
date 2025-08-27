<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @author      Sebastian Zoglowek    <https://github.com/zoglo>
 * @copyright   Oveleon               <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\Utils;

use Contao\System;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

/**
 * @internal
 */
trait TwigRenderTrait
{
    private static function renderTwigTemplate(string $template, array $data): string
    {
        /**
         * @var Environment $twig
         */
        if (!$twig = System::getContainer()->get('twig', ContainerInterface::NULL_ON_INVALID_REFERENCE))
        {
            return '';
        }

        $loader = $twig->getLoader();

        if ($loader->exists("@Contao/$template.html.twig"))
        {
            return $twig->render("@Contao/$template.html.twig", $data);
        }

        if ($loader->exists("@Contao_ContaoCookiebar/$template.html.twig"))
        {
            return $twig->render("@Contao_ContaoCookiebar/$template.html.twig", $data);
        }

        return '';
    }
}
