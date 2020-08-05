<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

\Contao\System::loadLanguageFile('tl_cookiebar');

// Palettes
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'activateCookiebar';
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'overwriteCookiebarMeta';

$GLOBALS['TL_DCA']['tl_page']['subpalettes']['activateCookiebar'] = 'cookiebarConfig,overwriteCookiebarMeta';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['overwriteCookiebarMeta'] = 'cookiebarDescription,cookiebarAlignment,cookiebarBlocking,cookiebarTemplate,cookiebarInfoUrls';

// Fields
$GLOBALS['TL_DCA']['tl_page']['fields']['activateCookiebar'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['activateCookiebar'],
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50', 'submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['overwriteCookiebarMeta'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['overwriteCookiebarMeta'],
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12', 'submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarConfig'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarConfig'],
    'inputType'               => 'select',
    'options_callback' => static function ()
    {
        return Oveleon\ContaoCookiebar\Cookiebar::getConfigurationList();
    },
    'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarDescription'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarDescription'],
    'inputType'               => 'textarea',
    'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true),
    'explanation'             => 'insertTags',
    'sql'                     => "mediumtext NULL"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarInfoUrls'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarInfoUrls'],
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50 clr'),
    'sql'                     => "blob NULL",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarTemplate'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarTemplate'],
    'inputType'               => 'select',
    'options_callback' => static function ()
    {
        return Contao\Controller::getTemplateGroup('cookiebar_');
    },
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarAlignment'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarAlignment'],
    'inputType'               => 'select',
    'options'                 => array('cc-top', 'cc-middle', 'cc-bottom'),
    'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarBlocking'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarBlocking'],
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
);

// Extend the default palettes
$objPaletteManipulator = Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('cookiebar_legend', 'global_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addField(array('activateCookiebar'), 'cookiebar_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
;

if (array_key_exists('rootfallback', $GLOBALS['TL_DCA']['tl_page']['palettes'])) {
    $objPaletteManipulator->applyToPalette('rootfallback', 'tl_page');
}
