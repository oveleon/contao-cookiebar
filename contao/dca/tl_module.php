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
$GLOBALS['TL_DCA']['tl_module']['palettes']['cookiebar_opener'] = '{title_legend},name,headline,type;{link_legend},linkTitle,titleText,prefillCookies;{template_legend:collapsed},customTpl;{protected_legend:collapsed},protected;{expert_legend:collapsed},guests,cssID';

// Fields
$GLOBALS['TL_DCA']['tl_module']['fields']['titleText'] = [
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => ['maxlength' => 255, 'tl_class' => 'w50'],
    'sql'                     => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['linkTitle'] = [
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => ['maxlength' => 255, 'tl_class' => 'w50'],
    'sql'                     => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['prefillCookies'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => ['tl_class' => 'w50 m12'],
    'sql'                     => "char(1) NOT NULL default ''",
];
