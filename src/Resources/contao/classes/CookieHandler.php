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
 * @property string  $scriptConfirmed
 * @property string  $scriptUnconfirmed
 * @property string  $scriptPosition
 * @property string  $scriptConfig
 * @property boolean $published
 */
class CookieHandler extends System
{
    /**
     * Position flag: Below the content within the body tag
     * @var integer
     */
    const POS_BELOW = 1;

    /**
     * Position flag: Above the content within the body tag
     * @var integer
     */
    const POS_ABOVE = 2;

    /**
     * Position flag: Within the head tag
     * @var integer
     */
    const POS_HEAD = 3;

    /**
     * Loading flag: Load only after confirmation
     * @var integer
     */
    const LOAD_CONFIRMED = 1;

    /**
     * Loading flag: Load only if not confirmed
     * @var integer
     */
    const LOAD_UNCONFIRMED = 2;

    /**
     * Loading flag: Load always
     * @var integer
     */
    const LOAD_ALWAYS = 3;

    /**
     * Model
     * @var CookieModel
     */
    protected $objModel;

    /**
     * Locked state
     * @var boolean
     */
    protected $isLocked = false;

    /**
     * Resource scripts
     * @var array
     */
    protected $resources = [];

    /**
     * Plain scripts
     * @var array
     */
    protected $scripts = [];

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

        switch($objCookie->type)
        {
            case 'script':
                $this->compileScript();
                break;
            case 'googleAnalytics':
                $this->compileGoogleAnalytics();
                break;
            case 'facebookPixel':
                $this->compileFacebookPixel();
                break;
            case 'matomo':
                $this->compileMatomo();
                break;
            default:
                // HOOK: allow to compile custom types
                if (isset($GLOBALS['TL_HOOKS']['compileCookieType']) && \is_array($GLOBALS['TL_HOOKS']['compileCookieType']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['compileCookieType'] as $callback)
                    {
                        $this->import($callback[0]);
                        $this->{$callback[0]}->{$callback[1]}($objCookie->type, $this);
                    }
                }
        }

        parent::__construct();
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
     * Add a script
     *
     * @param string $strScript
     * @param bool $confirmed
     * @param int $pos
     */
    public function addScript(string $strScript, bool $confirmed = true, int $pos = self::POS_BELOW): void
    {
        $this->scripts[] = [
            'script'    => $strScript,
            'position'  => $pos,
            'confirmed' => $confirmed
        ];
    }

    /**
     * Add a resource
     *
     * @param string $strSrc
     * @param array|null $flags
     * @param int $mode
     */
    public function addResource(string $strSrc, array $flags=null, int $mode = self::LOAD_CONFIRMED): void
    {
        $this->resources[] = [
            'src'   => $strSrc,
            'flags' => $flags,
            'mode'  => $mode
        ];
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

        if($src = $this->scriptConfirmed)
        {
            $this->addScript($src, true, $this->scriptPosition);
        }

        if($src = $this->scriptUnconfirmed)
        {
            $this->addScript($src, false, $this->scriptPosition);
        }
    }

    /**
     * Compile cookie of type "googleAnalytics"
     */
    private function compileGoogleAnalytics()
    {
        $this->addResource(
            'https://www.googletagmanager.com/gtag/js?id=' . $this->vendorId,
            null,
            1
        );

        $this->addScript(
            "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)} gtag('js',new Date());gtag('config','" . $this->vendorId . "'" . ($this->scriptConfig ? ' ,' . $this->scriptConfig : '') . ")",
            true,
            3
        );
    }

    /**
     * Compile cookie of type "facebookPixel"
     */
    private function compileFacebookPixel()
    {
        $this->addScript(
            "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init', '" . $this->vendorId . "');fbq('track', 'PageView');",
            true,
            3
        );
    }

    /**
     * Compile cookie of type "matomo"
     */
    private function compileMatomo()
    {
        $url = substr($this->vendorUrl, -1) === '/' ? $this->vendorUrl : $this->vendorUrl . '/';

        $this->addScript(
            "var _paq = window._paq = window._paq || []; _paq.push(['trackPageView']); _paq.push(['enableLinkTracking']); (function() { var u='" . $url . "'; _paq.push(['setTrackerUrl', u+'matomo.php']); _paq.push(['setSiteId', " . $this->vendorId . "]); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);})();",
            true,
            3
        );
    }
}
