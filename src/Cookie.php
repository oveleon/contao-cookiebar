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

use Contao\BackendTemplate;
use Contao\Environment;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoCookiebar\Model\CookieModel;

/**
 * Arranges the properties and resources of a cookie
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $tstamp
 * @property integer $identifier
 * @property integer $sorting
 * @property integer $expireTime
 * @property integer $provider
 * @property string  $title
 * @property string  $token
 * @property string  $type
 * @property string  $vendorId
 * @property string  $vendorUrl
 * @property string  $description
 * @property string  $sourceUrl
 * @property integer $sourceLoadingMode
 * @property string  $sourceUrlParameter
 * @property boolean $sourceVersioning
 * @property string  $scriptConfirmed
 * @property string  $scriptUnconfirmed
 * @property string  $scriptPosition
 * @property string  $scriptConfig
 * @property string  $scriptTemplate
 * @property string  $blockTemplate
 * @property string  $gcmMode
 * @property integer $globalConfig
 * @property boolean $disabled
 * @property boolean $published
 */
class Cookie extends AbstractCookie
{
    /**
     * Model
     */
    protected CookieModel $objModel;

    /**
     * Locked state
     */
    protected bool $isLocked = false;

    /**
     * Disabled state
     */
    protected bool $isDisabled = false;

    /**
     * Initialize the object
     *
     * @param CookieModel $objCookie
     */
    public function __construct(CookieModel $objCookie)
    {
        $this->objModel = $objCookie;

        if($objCookie->identifier === 'lock')
        {
            $this->isLocked = true;
        }

        if(!!$objCookie->disabled)
        {
            $this->isDisabled = true;
        }

        switch($objCookie->type)
        {
            case 'script':
                $this->addCustomScript();
                break;
            case 'template':
                $this->addCustomTemplate();
                break;
            case 'googleAnalytics':
                $this->addGoogleAnalytics();
                break;
            case 'googleConsentMode':
                $this->addGoogleConsentMode();
                break;
            case 'facebookPixel':
                $this->addFacebookPixel();
                break;
            case 'matomo':
                $this->addMatomo();
                break;
            case 'matomoTagManager':
                $this->addMatomoTagManager();
                break;
            case 'etracker':
                $this->addEtracker();
                break;
            default:
                // HOOK: allow to compile custom types
                if (isset($GLOBALS['TL_HOOKS']['addCookieType']) && \is_array($GLOBALS['TL_HOOKS']['addCookieType']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['addCookieType'] as $callback)
                    {
                        System::importStatic($callback[0])->{$callback[1]}($objCookie->type, $this);
                    }
                }
        }
    }

    /**
     * Return an object property
     */
    public function __get(string $strKey): mixed
    {
        if(isset($this->{$strKey}))
        {
            return $this->{$strKey};
        }

        return $this->objModel->{$strKey} ?? null;
    }

    /**
     * Compile cookie of type "script"
     */
    private function addCustomScript(): void
    {
        if($src = $this->sourceUrl)
        {
            if ($this->sourceVersioning)
            {
                $src .= (str_contains($src, '?') ? '&' : '?') . 'v=' . substr(md5(time()),0, 8);
            }

            $this->addResource(
                $src,
                StringUtil::deserialize($this->sourceUrlParameter) ?: null,
                $this->sourceLoadingMode
            );
        }

        if($src = $this->scriptConfirmed)
        {
            $this->addScript($src, self::LOAD_CONFIRMED, $this->scriptPosition);
        }

        if($src = $this->scriptUnconfirmed)
        {
            $this->addScript($src, self::LOAD_UNCONFIRMED, $this->scriptPosition);
        }
    }

    /**
     * Compile cookie of type "template"
     */
    private function addCustomTemplate(): void
    {
        $objTemplate = new BackendTemplate($this->scriptTemplate);
        $strTemplate = $objTemplate->parse();

        if (empty($strTemplate)) {
            return;
        }

        $doc = new \DOMDocument();

        libxml_use_internal_errors(true);
        $doc->loadHTML($strTemplate);
        libxml_clear_errors();

        $scripts = $doc->getElementsByTagName('script');

        if ($scripts->length > 0)
        {
            foreach ($scripts as $script)
            {
                $this->addScript($script->nodeValue, self::LOAD_CONFIRMED, $this->scriptPosition);
            }
        }
    }

    /**
     * Compile cookie of type "googleAnalytics"
     */
    private function addGoogleAnalytics(): void
    {
        $this->addResource(
            'https://www.googletagmanager.com/gtag/js?id=' . $this->vendorId,
            null,
            self::LOAD_CONFIRMED
        );

        $this->addScript(
            "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)} gtag('js',new Date());gtag('config','" . $this->vendorId . "'" . ($this->scriptConfig ? ' ,' . $this->scriptConfig : '') . ")",
            self::LOAD_CONFIRMED,
            self::POS_HEAD
        );

        # Determine the G-ID to ensure the opt-out (#127)
        $this->addScript(
            "try{ let keys = []; Object.keys(window.google_tag_manager).forEach((key) => { if(key.indexOf('G-') === 0 || key.indexOf('GTM-') === 0){ window['ga-disable-' + key] = true; } }); }catch (e) {}",
            self::LOAD_UNCONFIRMED,
            self::POS_HEAD
        );
    }

    /**
     * Compile cookie of type "googleConsentMode"
     */
    private function addGoogleConsentMode(): void
    {
        if($src = $this->scriptConfig)
        {
            $this->addScript($src, self::LOAD_CONFIRMED, self::POS_HEAD);
        }
        else
        {
            $this->addScript("gtag('consent', 'update', { '" . $this->gcmMode . "': 'granted' });", self::LOAD_CONFIRMED, self::POS_HEAD);
        }
    }

    /**
     * Compile cookie of type "facebookPixel"
     */
    private function addFacebookPixel(): void
    {
        $this->addScript(
            "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init', '" . $this->vendorId . "');fbq('track', 'PageView');",
            self::LOAD_CONFIRMED,
            self::POS_HEAD
        );
    }

    /**
     * Compile cookie of type "matomo"
     */
    private function addMatomo(): void
    {
        $url = str_ends_with($this->vendorUrl, '/') ? $this->vendorUrl : $this->vendorUrl . '/';

        $this->addScript(
            "var _paq = window._paq = window._paq || []; " . ($this->scriptConfig ?: "_paq.push(['trackPageView']); _paq.push(['enableLinkTracking']);") . " (function() { var u='" . $url . "'; _paq.push(['setTrackerUrl', u+'matomo.php']); _paq.push(['setSiteId', " . $this->vendorId . "]); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);})();",
            self::LOAD_CONFIRMED,
            self::POS_HEAD
        );
    }

    /**
     * Compile cookie of type "matomo tag manager"
     */
    private function addMatomoTagManager(): void
    {
        // Custom config
        if($src = $this->scriptConfig)
        {
            $this->addScript(
                $src,
                self::LOAD_CONFIRMED,
                self::POS_HEAD
            );
        }

        $url = str_ends_with($this->vendorUrl, '/') ? $this->vendorUrl : $this->vendorUrl . '/';

        $this->addScript(
            " var _mtm = window._mtm = window._mtm || []; _mtm.push({'mtm.startTime': (new Date().getTime()), 'event': 'mtm.Start'}); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.async=true; g.src='https://".$url."js/container_".$this->vendorId.".js'; s.parentNode.insertBefore(g,s);",
            self::LOAD_CONFIRMED,
            self::POS_HEAD
        );
    }

    /**
     * Compile cookie of type "etracker"
     */
    private function addEtracker(): void
    {
        // Custom config
        if($src = $this->scriptConfig)
        {
            $this->addScript(
                $src,
                self::LOAD_ALWAYS,
                self::POS_HEAD
            );
        }

        $this->addResource(
            '//code.etracker.com/code/e.js',
            [
                'async',
                ['id', '_etLoader'],
                ['data-block-cookies', 'true'],
                ['data-secure-code', $this->vendorId],
                ['data-respect-dnt', System::getContainer()->getParameter('contao_cookiebar.consider_dnt')]
            ],
            self::LOAD_ALWAYS
        );

        if(!$this->blockCookies)
        {
            $script = "cookiebar.onResourceLoaded(".$this->id.", function(){ _etracker.enableCookies('".Environment::get('host')."');});";

            $this->addScript(
                "if(cookiebar && typeof cookiebar.onResourceLoaded === 'function'){ $script } else { document.addEventListener('DOMContentLoaded',function(){ $script }); }",
                self::LOAD_CONFIRMED,
                self::POS_BELOW
            );
        }
    }
}
