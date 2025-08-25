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

// Palette
$GLOBALS['TL_DCA']['tl_content']['palettes']['cookiebar_opener'] = '{type_legend},type,headline;{link_legend},linkTitle,titleText,prefillCookies;{template_legend:collapsed},customTpl;{protected_legend:collapsed},protected;{expert_legend:collapsed},guests,cssID;{invisible_legend:collapsed},invisible,start,stop';

// Fields
$GLOBALS['TL_DCA']['tl_content']['fields']['prefillCookies'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => ['tl_class' => 'w50 m12'],
    'sql'                     => "char(1) NOT NULL default ''",
];
