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

// Back end modules
$GLOBALS['BE_MOD']['system']['cookiebar'] = [
    'tables' => [
        'tl_cookiebar',
        'tl_cookie_log',
        'tl_cookie_group',
        'tl_cookie',
        'tl_cookie_config',
    ],
    'export' => [\Oveleon\ContaoCookiebar\Export\LogExport::class, 'export'],
];

// Models
$GLOBALS['TL_MODELS']['tl_cookiebar']     = \Oveleon\ContaoCookiebar\Model\CookiebarModel::class;
$GLOBALS['TL_MODELS']['tl_cookie_log']    = \Oveleon\ContaoCookiebar\Model\CookieLogModel::class;
$GLOBALS['TL_MODELS']['tl_cookie_group']  = \Oveleon\ContaoCookiebar\Model\CookieGroupModel::class;
$GLOBALS['TL_MODELS']['tl_cookie']        = \Oveleon\ContaoCookiebar\Model\CookieModel::class;
$GLOBALS['TL_MODELS']['tl_cookie_config'] = \Oveleon\ContaoCookiebar\Model\GlobalConfigModel::class;
