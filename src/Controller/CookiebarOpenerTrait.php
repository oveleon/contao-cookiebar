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

namespace Oveleon\ContaoCookiebar\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\String\HtmlAttributes;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CookiebarOpenerTrait
{
    protected function getResponse(FragmentTemplate $template, ContentModel|ModuleModel $model, Request $request): Response
    {
        $title = StringUtil::specialchars($model->titleText);

        $attributes = (new HtmlAttributes())
            ->addClass(['ccb-trigger', $model->prefillCookies ? 'ccb-prefill' : ''])
            ->set('href', 'javascript:;')
            ->set('rel', 'noreferrer noopener')
            ->set('title', $title, !empty($title))
        ;

        $template->set('attributes', $attributes);

        return $template->getResponse();
    }
}
