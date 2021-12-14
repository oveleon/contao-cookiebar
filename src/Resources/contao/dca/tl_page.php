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
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'triggerCookiebar';

$GLOBALS['TL_DCA']['tl_page']['subpalettes']['activateCookiebar'] = 'cookiebarConfig,overwriteCookiebarMeta';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['overwriteCookiebarMeta'] = 'cookiebarDescription,cookiebarInfoDescription,cookiebarAlignment,cookiebarBlocking,cookiebarButtonColorScheme,cookiebarTemplate,cookiebarInfoUrls,cookiebarExcludePages';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['triggerCookiebar'] = 'prefillCookies';

// Callbacks
$GLOBALS['TL_DCA']['tl_page']['fields']['cssClass']['eval']['alwaysSave'] = true;
$GLOBALS['TL_DCA']['tl_page']['fields']['cssClass']['load_callback'][] = array('Oveleon\ContaoCookiebar\EventListener\DataContainer\PageCallbackListener', 'onLoadCssClass');
$GLOBALS['TL_DCA']['tl_page']['fields']['cssClass']['save_callback'][] = array('Oveleon\ContaoCookiebar\EventListener\DataContainer\PageCallbackListener', 'onSaveCssClass');

// Fields
$GLOBALS['TL_DCA']['tl_page']['fields']['activateCookiebar'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['activateCookiebar'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50', 'submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['triggerCookiebar'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['triggerCookiebar'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50', 'submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['prefillCookies'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['prefillCookies'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "char(1) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_page']['fields']['overwriteCookiebarMeta'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['overwriteCookiebarMeta'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12', 'submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarConfig'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarConfig'],
    'exclude'                 => true,
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
    'exclude'                 => true,
    'inputType'               => 'textarea',
    'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class' => 'w50'),
    'explanation'             => 'insertTags',
    'sql'                     => "mediumtext NULL"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarInfoDescription'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarInfoDescription'],
    'exclude'                 => true,
    'inputType'               => 'textarea',
    'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class' => 'w50'),
    'explanation'             => 'insertTags',
    'sql'                     => "mediumtext NULL"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarInfoUrls'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarInfoUrls'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50 clr'),
    'sql'                     => "blob NULL",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarExcludePages'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarExcludePages'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50'),
    'sql'                     => "blob NULL",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarTemplate'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarTemplate'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback' => static function ()
    {
        return Contao\Controller::getTemplateGroup('cookiebar_');
    },
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarButtonColorScheme'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarButtonColorScheme'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => ['grayscale', 'highlight'],
    'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
    'eval'                    => array('includeBlankOption'=>true, 'blankOptionLabel'=>$GLOBALS['TL_LANG']['tl_cookiebar']['neutral'], 'tl_class'=>'w50 clr'),
    'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarAlignment'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarAlignment'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => array(
        'cc-top'             => 'align-top',
        'cc-top cc-left'     => 'align-top-left',
        'cc-top cc-right'    => 'align-top-right',
        'cc-middle'          => 'align-middle',
        'cc-bottom'          => 'align-bottom',
        'cc-bottom cc-left'  => 'align-bottom-left',
        'cc-bottom cc-right' => 'align-bottom-right'
    ),
    'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
    'eval'                    => array('tl_class'=>'w50 clr'),
    'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['cookiebarBlocking'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['cookiebarBlocking'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
);

// Extend the default palettes
$objPaletteManipulator = Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('cookiebar_legend', 'global_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addField(['activateCookiebar'], 'cookiebar_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
;

if (array_key_exists('rootfallback', $GLOBALS['TL_DCA']['tl_page']['palettes'])) {
    $objPaletteManipulator->applyToPalette('rootfallback', 'tl_page');
}

$objPaletteManipulator = Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('cookiebar_legend', 'redirect_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addField(['triggerCookiebar'], 'cookiebar_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('forward', 'tl_page')
;
