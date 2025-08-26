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

use Contao\System;
use Contao\CoreBundle\DataContainer\PaletteManipulator;

System::loadLanguageFile('tl_cookiebar');

// Palettes
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'activateCookiebar';
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'overwriteCookiebarMeta';
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'triggerCookiebar';

$GLOBALS['TL_DCA']['tl_page']['subpalettes']['activateCookiebar']      = 'cookiebarConfig,overwriteCookiebarMeta';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['overwriteCookiebarMeta'] = 'cookiebarDescription,cookiebarInfoDescription,cookiebarAlignment,cookiebarButtonColorScheme,cookiebarBlocking,cookiebarHideOnInit,cookiebarInfoUrls,cookiebarExcludePages,cookiebarTemplate';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['triggerCookiebar']       = 'prefillCookies';

// Overwrite cssClass eval
$GLOBALS['TL_DCA']['tl_page']['fields']['cssClass']['eval']['alwaysSave'] = true;

// Fields
$GLOBALS['TL_DCA']['tl_page']['fields']['activateCookiebar'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => ['tl_class' => 'w50', 'submitOnChange' => true],
    'sql'                     => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['triggerCookiebar'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => ['tl_class' => 'w50', 'submitOnChange' => true],
    'sql'                     => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['prefillCookies'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => ['tl_class' => 'w50'],
    'sql'                     => ['type' => 'boolean', 'default' => false],
];


$GLOBALS['TL_DCA']['tl_page']['fields']['overwriteCookiebarMeta'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
    'sql'                     => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarConfig'] = [
    'exclude'                 => true,
    'inputType'               => 'select',
    'eval'                    => ['mandatory' => true, 'tl_class' => 'w50'],
    'sql'                     => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarDescription'] = [
    'exclude'                 => true,
    'inputType'               => 'textarea',
    'eval'                    => ['rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'w50'],
    'explanation'             => 'insertTags',
    'sql'                     => "mediumtext NULL",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarInfoDescription'] = [
    'exclude'                 => true,
    'inputType'               => 'textarea',
    'eval'                    => ['rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'w50'],
    'explanation'             => 'insertTags',
    'sql'                     => "mediumtext NULL",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarInfoUrls'] = [
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'w50 clr'],
    'relation'                => ['type' => 'hasOne', 'load' => 'lazy'],
    'sql'                     => "blob NULL",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarExcludePages'] = [
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'w50'],
    'relation'                => ['type' => 'hasOne', 'load' => 'lazy'],
    'sql'                     => "blob NULL",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarTemplate'] = [
    'exclude'                 => true,
    'inputType'               => 'select',
    'eval'                    => ['tl_class' => 'w50'],
    'sql'                     => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarButtonColorScheme'] = [
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => ['grayscale', 'highlight'],
    'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
    'eval'                    => ['includeBlankOption' => true, 'blankOptionLabel' => $GLOBALS['TL_LANG']['tl_cookiebar']['neutral'], 'tl_class' => 'w50'],
    'sql'                     => "varchar(32) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarAlignment'] = [
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => [
        'cc-top'             => 'align-top',
        'cc-top cc-left'     => 'align-top-left',
        'cc-top cc-right'    => 'align-top-right',
        'cc-middle'          => 'align-middle',
        'cc-bottom'          => 'align-bottom',
        'cc-bottom cc-left'  => 'align-bottom-left',
        'cc-bottom cc-right' => 'align-bottom-right',
    ],
    'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
    'eval'                    => ['tl_class' => 'w50 clr'],
    'sql'                     => "varchar(32) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarBlocking'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => ['tl_class' => 'w50 clr'],
    'sql'                     => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarHideOnInit'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => ['tl_class' => 'w50'],
    'sql'                     => ['type' => 'boolean', 'default' => false],
];

// Extend the default palettes
$objPaletteManipulator = PaletteManipulator::create()
    ->addLegend('cookiebar_legend', 'global_legend', PaletteManipulator::POSITION_AFTER, true)
    ->addField(['activateCookiebar'], 'cookiebar_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
;

if (array_key_exists('rootfallback', $GLOBALS['TL_DCA']['tl_page']['palettes']))
{
    $objPaletteManipulator->applyToPalette('rootfallback', 'tl_page');
}

$objPaletteManipulator = PaletteManipulator::create()
    ->addLegend('cookiebar_legend', 'redirect_legend', PaletteManipulator::POSITION_AFTER, true)
    ->addField(['triggerCookiebar'], 'cookiebar_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('forward', 'tl_page')
;
