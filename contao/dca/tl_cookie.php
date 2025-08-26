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

use Contao\DataContainer;
use Contao\DC_Table;
use Oveleon\ContaoCookiebar\AbstractCookie;

$GLOBALS['TL_DCA']['tl_cookie'] = [
    // Palettes
    'palettes' => [
        '__selector__'                => ['type'],
        'default'                     => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{description_legend:collapsed},description,detailDescription;{expert_legend:collapsed},disabled;{publish_legend},published,checked;',
        'script'                      => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{global_config_legend:collapsed},globalConfig;sourceUrl,sourceLoadingMode,sourceUrlParameter,sourceVersioning;scriptConfirmed,scriptUnconfirmed,scriptPosition;{description_legend:collapsed},description,detailDescription;{expert_legend:collapsed},priority;{publish_legend},published,checked;',
        'template'                    => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{global_config_legend:collapsed},globalConfig;{template_legend},scriptTemplate,scriptPosition;{description_legend:collapsed},description,detailDescription;{expert_legend:collapsed},priority;{publish_legend},published,checked;',
        'googleAnalytics'             => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{google_analytics_legend},vendorId,scriptConfig;{description_legend:collapsed},description,detailDescription;{expert_legend:collapsed},priority;{publish_legend},published,checked;',
        'googleConsentMode'           => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{google_consent_mode_legend},globalConfig,vendorId,gcmMode,alwaysLoadTagJS,scriptConfig;{description_legend:collapsed},description,detailDescription;{expert_legend:collapsed},priority;{publish_legend},published,checked;',
        'facebookPixel'               => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{facebook_pixel_legend},vendorId;{description_legend:collapsed},description,detailDescription;{expert_legend:collapsed},priority;{publish_legend},published,checked;',
        'matomo'                      => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{matomo_legend},vendorId,vendorUrl,scriptConfig;{description_legend:collapsed},description,detailDescription;{expert_legend:collapsed},priority;{publish_legend},published,checked;',
        'matomoTagManager'            => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{matomo_legend},vendorId,vendorUrl,scriptConfig;{description_legend:collapsed},description,detailDescription;{expert_legend:collapsed},priority;{publish_legend},published,checked;',
        'etracker'                    => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{matomo_legend},vendorId,blockCookies,scriptConfig;{description_legend:collapsed},description,detailDescription;{expert_legend:collapsed},priority;{publish_legend},published,checked;',
        'iframe'                      => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{iframe_legend},iframeType,blockTemplate,blockDescription;{description_legend:collapsed},description,detailDescription;{expert_legend:collapsed},priority;{publish_legend},published,checked;',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql'                     => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid' => [
            'foreignKey'              => 'tl_cookie_group.title',
            'sql'                     => "int(10) unsigned NOT NULL default 0",
            'relation'                => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'sorting' => [
            'sql'                     => "int(10) unsigned NOT NULL default 0",
        ],
        'identifier' => [
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'tstamp' => [
            'sql'                     => "int(10) unsigned NOT NULL default 0",
        ],
        'title' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'token' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'showTokens' => [
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['doNotCopy' => true, 'tl_class' => 'w50 m12'],
            'sql'                     => ['type' => 'boolean', 'default' => false],
        ],
        'expireTime' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'showExpireTime' => [
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['doNotCopy' => true, 'tl_class' => 'w50 m12'],
            'sql'                     => ['type' => 'boolean', 'default' => true],
        ],
        'provider' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'showProvider' => [
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['doNotCopy' => true, 'tl_class' => 'w50 m12'],
            'sql'                     => ['type' => 'boolean', 'default' => true],
        ],
        'type' => [
            'exclude'                 => true,
            'filter'                  => true,
            'default'                 => 'default',
            'inputType'               => 'select',
            'options'        => [
                'default',
                'script',
                'template',
                'googleAnalytics',
                'googleConsentMode',
                'facebookPixel',
                'matomo',
                'matomoTagManager',
                'etracker',
                'iframe',
            ],
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
            'eval'                    => ['helpwizard' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'                     => ['name' => 'type', 'type' => 'string', 'length' => 64, 'default' => 'text'],
        ],
        'iframeType' => [
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => ['name' => 'iframeType', 'type' => 'string', 'length' => 64, 'default' => ''],
        ],
        'blockTemplate' => [
            'default'                 => 'ccb/element_blocker',
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => "varchar(64) NOT NULL default ''",
        ],
        'scriptTemplate' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'eval'                    => ['tl_class' => 'w50', 'mandatory' => true],
            'sql'                     => "varchar(64) NOT NULL default ''",
        ],
        'vendorId' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'vendorUrl' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp' => 'httpurl', 'mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'description' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'w50'],
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL",
        ],
        'detailDescription' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'w50'],
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL",
        ],
        'blockDescription' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'clr'],
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL",
        ],
        'sourceUrl' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'dcaPicker'=> ['do' => 'files', 'context' => 'file', 'icon' => 'pickfile.svg', 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => 'js'], 'addWizardClass' => false, 'tl_class' => 'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'sourceLoadingMode' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'options'                 => [
                AbstractCookie::LOAD_CONFIRMED   => 'confirmed',
                AbstractCookie::LOAD_UNCONFIRMED => 'unconfirmed',
                AbstractCookie::LOAD_ALWAYS      => 'always',
            ],
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => "char(1) NOT NULL default ''",
        ],
        'sourceUrlParameter' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'checkbox',
            'options'                 => ['async', 'defer'],
            'eval'                    => ['multiple' => true, 'tl_class' => 'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'sourceVersioning' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => ['type' => 'boolean', 'default' => false],
        ],
        'scriptConfirmed' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['preserveTags' => true, 'decodeEntities' => true, 'class' => 'monospace', 'rte' => 'ace|javascript', 'tl_class' => 'clr'],
            'sql'                     => "text NULL",
        ],
        'scriptUnconfirmed' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['preserveTags' => true, 'decodeEntities' => true, 'class' => 'monospace', 'rte' => 'ace|javascript', 'tl_class' => 'clr'],
            'sql'                     => "text NULL",
        ],
        'scriptPosition' => [
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => [
                AbstractCookie::POS_BELOW => 'bodyBelowContent',
                AbstractCookie::POS_ABOVE => 'bodyAboveContent',
                AbstractCookie::POS_HEAD  => 'head',
            ],
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => "varchar(32) NOT NULL default ''",
        ],
        'scriptConfig' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['preserveTags' => true, 'decodeEntities' => true, 'helpwizard' => true, 'class' => 'monospace', 'rte' => 'ace|javascript', 'tl_class' => 'clr'],
            'sql'                     => "text NULL",
            'explanation'             => 'cookiebarScriptConfig',
        ],
        'globalConfig' => [
            'exclude'                 => true,
            'inputType'               => 'picker',
            'foreignKey'              => 'tl_cookie_config.title',
            'eval'                    => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'                     => "int(10) unsigned NOT NULL default 0",
            'relation'                => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'gcmMode' => [
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'options'                 => ['ad_storage', 'ad_user_data', 'ad_personalization', 'analytics_storage', 'functionality_storage', 'personalization_storage', 'security_storage'],
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
            'eval'                    => ['submitOnChange' => true, 'multiple' => true, 'tl_class' => 'w50 clr'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'alwaysLoadTagJS' => [
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => ['type' => 'boolean', 'default' => false],
        ],
        'disabled' => [
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => ['type' => 'boolean', 'default' => false],
        ],
        'priority' => [
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => "int(10) NOT NULL default 0",
        ],
        'checked' => [
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => ['type' => 'boolean', 'default' => false],
        ],
        'blockCookies' => [
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    =>['tl_class' => 'w50'],
            'sql'                     => ['type' => 'boolean', 'default' => false],
        ],
        'published' => [
            'exclude'                 => true,
            'filter'                  => true,
            'toggle'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['doNotCopy' => true, 'tl_class' => 'w50'],
            'sql'                     => ['type' => 'boolean', 'default' => false],
        ],
    ],

    // Config
    'config' => [
        'dataContainer'               => DC_Table::class,
        'ptable'                      => 'tl_cookie_group',
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,published' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode'                    => DataContainer::MODE_PARENT,
            'fields'                  => ['sorting'],
            'headerFields'            => ['title'],
            'panelLayout'             => 'limit',
            'child_record_class'      => 'no_padding',
        ],
        'label' => [
            'fields'                  => ['title'],
            'format'                  => '%s',
        ],
        'global_operations' => [
            'all',
        ],
        'operations' => [
            'edit',
            'copy',
            'cut',
            'delete',
            'show',
            'toggle',
        ],
    ],
];
