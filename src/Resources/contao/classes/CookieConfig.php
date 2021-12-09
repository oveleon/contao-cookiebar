<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar;

use Contao\StringUtil;
use Contao\System;

/**
 * Arranges the properties and resources of a config
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $title
 * @property string  $type
 * @property string  $vendorId
 * @property string  $sourceUrl
 * @property integer $sourceLoadingMode
 * @property string  $sourceUrlParameter
 * @property string  $scriptPosition
 * @property integer $scriptLoadingMode
 * @property string  $scriptConfig
 * @property boolean $googleConsentMode
 * @property boolean $published
 */
class CookieConfig extends AbstractCookie
{
    /**
     * Model
     * @var CookieModel
     */
    protected $objModel;

    /**
     * Collection of cookie IDs which use this configuration
     * @var array
     */
    protected $arrCookies = array();

    /**
     * Initialize the object
     *
     * @param CookieConfigModel $objConfig
     */
    public function __construct(CookieConfigModel $objConfig)
    {
        $this->objModel = $objConfig;

        switch($objConfig->type)
        {
            case 'script':
                $this->compileScript();
                break;
            case 'tagManager':
                $this->compileTagManager();
                break;
            default:
                // HOOK: allow to compile custom types
                if (isset($GLOBALS['TL_HOOKS']['compileCookieConfigType']) && \is_array($GLOBALS['TL_HOOKS']['compileCookieConfigType']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['compileCookieConfigType'] as $callback)
                    {
                        System::importStatic($callback[0])->{$callback[1]}($objConfig->type, $this);
                    }
                }
        }
    }

    /**
     * Return an object property
     *
     * @param string $strKey The property name
     *
     * @return mixed|null The property value or null
     */
    public function __get($strKey)
    {
        if($this->{$strKey})
        {
            return $this->{$strKey};
        }

        return $this->objModel->{$strKey} ?? null;
    }

    /**
     * Adds a cookie to the configuration
     *
     * @param CookieModel $objCookie The cookie model
     */
    public function addCookie(CookieModel $objCookie)
    {
        $this->arrCookies[ $objCookie->id ] = $objCookie;
    }

    /**
     * Compile cookie of type "script"
     */
    private function compileScript()
    {
        if($src = $this->sourceUrl)
        {
            $this->addResource(
                $src,
                StringUtil::deserialize($this->sourceUrlParameter) ?: null,
                $this->sourceLoadingMode
            );
        }

        if($src = $this->scriptConfig)
        {
            $this->addScript($src, $this->scriptLoadingMode, $this->scriptPosition);
        }
    }

    /**
     * Compile config of type "tagManager"
     */
    private function compileTagManager()
    {
        $this->addResource(
            'https://www.googletagmanager.com/gtag/js?id=' . $this->vendorId,
            ['async'],
            self::LOAD_ALWAYS
        );

        if($src = $this->scriptConfig)
        {
            $this->addScript($src, self::LOAD_ALWAYS, self::POS_HEAD);
        }
        elseif($this->googleConsentMode)
        {
            $this->compileGoogleConsentMode();
        }
    }

    /**
     * Compile config of type "tagManager"
     */
    private function compileGoogleConsentMode()
    {
        $this->addScript(
            "window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('consent', 'default', { 'ad_storage': 'denied', 'analytics_storage': 'denied', 'wait_for_update': 500 }); gtag('js', new Date()); gtag('config', '" . $this->vendorId . "');",
            self::LOAD_ALWAYS,
            self::POS_HEAD
        );
    }
}
