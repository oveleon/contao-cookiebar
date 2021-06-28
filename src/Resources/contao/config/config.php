<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

// Back end modules
$GLOBALS['BE_MOD']['system']['cookiebar'] = array(
    'tables' => array('tl_cookiebar', 'tl_cookie_log', 'tl_cookie_group', 'tl_cookie'),
    'export' => array('Oveleon\ContaoCookiebar\LogExport', 'export'),
);

// Models
$GLOBALS['TL_MODELS']['tl_cookiebar'] = 'Oveleon\ContaoCookiebar\CookiebarModel';
$GLOBALS['TL_MODELS']['tl_cookie_log'] = 'Oveleon\ContaoCookiebar\CookieLogModel';
$GLOBALS['TL_MODELS']['tl_cookie_group'] = 'Oveleon\ContaoCookiebar\CookieGroupModel';
$GLOBALS['TL_MODELS']['tl_cookie'] = 'Oveleon\ContaoCookiebar\CookieModel';

// Front end modules
$GLOBALS['FE_MOD']['application']['cookiebarOpener'] = 'Oveleon\ContaoCookiebar\ModuleCookiebar';

// Content elements
$GLOBALS['TL_CTE']['links']['cookiebarOpener'] = 'Oveleon\ContaoCookiebar\ContentCookiebar';

// Hooks
$GLOBALS['TL_HOOKS']['outputFrontendTemplate'][] = array('Oveleon\ContaoCookiebar\EventListener\FrontendTemplateListener', 'onOutputFrontendTemplate');
$GLOBALS['TL_HOOKS']['parseFrontendTemplate'][] = array('Oveleon\ContaoCookiebar\EventListener\FrontendTemplateListener', 'onParseFrontendTemplate');

// Scripts
if (defined('TL_MODE') && TL_MODE == 'BE')
{
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaocookiebar/scripts/configPresets.min.js';
}
