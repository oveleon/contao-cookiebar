<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

// Palette
$GLOBALS['TL_DCA']['tl_module']['palettes']['cookiebar_opener'] = '{title_legend},name,headline,type;{link_legend},linkTitle,titleText,prefillCookies;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

// Fields
$GLOBALS['TL_DCA']['tl_module']['fields']['titleText'] = [
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => ['maxlength'=>255, 'tl_class'=>'w50'],
    'sql'                     => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['linkTitle'] = [
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => ['maxlength'=>255, 'tl_class'=>'w50'],
    'sql'                     => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['prefillCookies'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => ['tl_class'=>'w50 m12'],
    'sql'                     => "char(1) NOT NULL default ''"
];
