<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

use Oveleon\ContaoCookiebar\Export\LogExport;
use Oveleon\ContaoCookiebar\Model\CookiebarModel;
use Oveleon\ContaoCookiebar\Model\CookieLogModel;
use Oveleon\ContaoCookiebar\Model\CookieGroupModel;
use Oveleon\ContaoCookiebar\Model\CookieModel;
use Oveleon\ContaoCookiebar\Model\GlobalConfigModel;

// Back end modules
$GLOBALS['BE_MOD']['system']['cookiebar'] = [
    'tables' => [
        'tl_cookiebar',
        'tl_cookie_log',
        'tl_cookie_group',
        'tl_cookie',
        'tl_cookie_config'
    ],
    'export' => [LogExport::class, 'export']
];

// Models
$GLOBALS['TL_MODELS']['tl_cookiebar']     = CookiebarModel::class;
$GLOBALS['TL_MODELS']['tl_cookie_log']    = CookieLogModel::class;
$GLOBALS['TL_MODELS']['tl_cookie_group']  = CookieGroupModel::class;
$GLOBALS['TL_MODELS']['tl_cookie']        = CookieModel::class;
$GLOBALS['TL_MODELS']['tl_cookie_config'] = GlobalConfigModel::class;
