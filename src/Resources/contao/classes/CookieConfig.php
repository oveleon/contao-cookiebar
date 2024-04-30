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
 * @property boolean $sourceVersioning
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
        if($this->{$strKey} ?? null)
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
            if ($this->sourceVersioning)
            {
                $src .= (strpos($src, '?') !== false ? '&' : '?') . 'v=' . substr(md5(time()),0, 8);
            }

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
        # Determine the G-ID to ensure the opt-out (#127)
        $this->addScript(
            "window.addEventListener('gtm_loaded', () => { setTimeout(() => { const gid = Object.keys(window.google_tag_manager).filter(k => k.startsWith('G-'))[0]; try{ if(gid){ window['ga-disable-' + gid] = true; }   }catch(e){}}, 1000)});",
            self::LOAD_ALWAYS,
            self::POS_HEAD
        );

        $this->addScript(
            "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . $this->vendorId . "');",
            self::LOAD_ALWAYS,
            self::POS_HEAD
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
            "window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('consent', 'default', { 'ad_storage': 'denied', 'ad_user_data': 'denied', 'ad_personalization': 'denied', 'analytics_storage': 'denied', 'functionality_storage': 'denied', 'personalization_storage': 'denied', 'security_storage': 'denied', 'wait_for_update': 500 }); gtag('js', new Date()); gtag('config', '" . $this->vendorId . "');",
            self::LOAD_ALWAYS,
            self::POS_HEAD
        );
    }
}
