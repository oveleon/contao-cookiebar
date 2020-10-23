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

use Contao\Database;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoCookiebar\Exception\NoCookiebarSpecifiedException;

class Cookiebar
{
    /**
     * Config cache
     * @var null
     */
    static private $configCache = null;

    /**
     * Create and return config object
     *
     * @param integer $configId
     * @param null $objMeta
     *
     * @return object
     */
    public static function getConfig($configId, $objMeta=null)
    {
        if(null !== static::$configCache)
        {
            return static::$configCache;
        }

        $objCookiebar = CookiebarModel::findById($configId);

        if(null === $objCookiebar)
        {
            throw new NoCookiebarSpecifiedException('No cookiebar specified');
        }

        $objCookieGroups = CookieGroupModel::findPublishedByPid($objCookiebar->id);

        if(null === $objCookieGroups)
        {
            return null;
        }

        $objConfig = $objCookiebar->current();
        $arrGroups = [];

        // Overwrite metadata
        if(null !== $objMeta)
        {
            $objConfig->description     = $objMeta->cookiebarDescription;
            $objConfig->infoDescription = $objMeta->cookiebarInfoDescription;
            $objConfig->infoUrls        = $objMeta->cookiebarInfoUrls;
            $objConfig->excludePages    = $objMeta->cookiebarExcludePages;
            $objConfig->template        = $objMeta->cookiebarTemplate;
            $objConfig->alignment       = $objMeta->cookiebarAlignment;
            $objConfig->blocking        = $objMeta->cookiebarBlocking;
        }

        while($objCookieGroups->next())
        {
            $objGroup = $objCookieGroups->current();
            $arrCookies = [];

            $objCookies = CookieModel::findPublishedByPid($objCookieGroups->id);

            if(null !== $objCookies)
            {
                while($objCookies->next())
                {
                    if(
                        ($objCookies->token === 'csrf_contao_csrf_token' && Environment::get('ssl')) ||
                        ($objCookies->token === 'csrf_https-contao_csrf_token' && !Environment::get('ssl'))
                    )
                    {
                        continue;
                    }

                    $arrCookies[] = new CookieHandler($objCookies->current());
                }
            }

            $objGroup->hasCookies = count($arrCookies);
            $objGroup->isLocked = $objGroup->identifier === 'lock';
            $objGroup->cookies = $arrCookies;

            $arrGroups[] = $objGroup;
        }

        global $objPage;

        $objConfig->groups = $arrGroups;
        $objConfig->pageId = $objPage->rootId;

        // Cache config
        static::$configCache = $objConfig;

        return $objConfig;
    }

    /**
     * Return config by page
     *
     * @param $varPage
     *
     * @return object|null
     */
    public static function getConfigByPage($varPage)
    {
        if(!($varPage instanceof PageModel))
        {
              $objPage = PageModel::findById( $varPage );
        }else $objPage = $varPage;

        if(!$objPage->activateCookiebar)
        {
            return null;
        }

        return static::getConfig($objPage->cookiebarConfig, !!$objPage->overwriteCookiebarMeta ? $objPage : null);
    }

    /**
     * Returns all configurations [id => title]
     *
     * @return array
     */
    public static function getConfigurationList(): array
    {
        $objCookiebars = CookiebarModel::findAll();
        $arrCookiebars = [];

        if(null != $objCookiebars)
        {
            while($objCookiebars->next())
            {
                $arrCookiebars[ $objCookiebars->id ] = $objCookiebars->title;
            }
        }

        return $arrCookiebars;
    }

    /**
     * Return all iFrame-Types
     *
     * @return array|null
     */
    public static function getIframeTypes(): ?array
    {
        return System::getContainer()->getParameter('contao_cookiebar.iframe_types');
    }

    /**
     * Parse Cookiebar template
     *
     * @param $objConfig
     * @return string
     */
    public static function parseCookiebarTemplate($objConfig)
    {
        System::loadLanguageFile('tl_cookiebar');

        /** @var FrontendTemplate $objTemplate */
        $objTemplate = new FrontendTemplate($objConfig->template);

        $cssID = unserialize($objConfig->cssID);
        $objTemplate->cssID = $cssID[0];
        $objTemplate->class = $cssID[1] ? $objConfig->template . ' ' . $objConfig->alignment . ' ' . trim($cssID[1]) : $objConfig->template . ' ' . $objConfig->alignment;

        if($objConfig->blocking)
        {
            $objTemplate->class .= ' cc-blocked';
        }

        $objTemplate->description = $objConfig->description;
        $objTemplate->infoDescription = $objConfig->infoDescription;
        $objTemplate->groups = $objConfig->groups;

        $objTemplate->saveLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['saveLabel'];
        $objTemplate->acceptAllLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['acceptAllLabel'];
        $objTemplate->denyAllLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['denyAllLabel'];
        $objTemplate->infoLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['infoLabel'];
        $objTemplate->showDetailsLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['showDetailsLabel'];
        $objTemplate->hideDetailsLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['hideDetailsLabel'];
        $objTemplate->showMoreDetailsLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['showMoreDetailsLabel'];
        $objTemplate->hideMoreDetailsLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['hideMoreDetailsLabel'];
        $objTemplate->providerLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['providerLabel'];
        $objTemplate->expireLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['expireLabel'];
        $objTemplate->showTokenLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['showTokenLabel'];

        // Collect info links
        $arrLinks = [];

        if($varLinks = StringUtil::deserialize($objConfig->infoUrls))
        {
            foreach ($varLinks as $link) {
                $objPage = PageModel::findById($link);

                if(null !== $objPage)
                {
                    $arrLinks[ $objPage->title ] = $objPage->getAbsoluteUrl();
                }
            }
        }

        $objTemplate->infoUrls = $arrLinks;

        // HOOK: parseCookiebarTemplate
        if (isset($GLOBALS['TL_HOOKS']['parseCookiebarTemplate']) && \is_array($GLOBALS['TL_HOOKS']['parseCookiebarTemplate']))
        {
            foreach ($GLOBALS['TL_HOOKS']['parseCookiebarTemplate'] as $callback)
            {
                System::importStatic($callback[0])->{$callback[1]}($objTemplate, $objConfig);
            }
        }

        return $objTemplate->parse();
    }

    /**
     * Collect cookie scripts
     *
     * @param object $objConfig
     *
     * @return array
     */
    public static function validateCookies($objConfig): array
    {
        $arrResponse = [];

        foreach ($objConfig->groups as $group)
        {
            foreach ($group->cookies as $cookie)
            {
                if($cookie->isLocked || $cookie->type === 'default')
                {
                    continue;
                }

                $arrCookie = [
                    'id'        => $cookie->id,
                    'type'      => $cookie->type,
                    'token'     => static::parseToken($cookie->token),
                    'resources' => $cookie->resources,
                    'scripts'   => $cookie->scripts
                ];

                if($cookie->type === 'iframe')
                {
                    $arrCookie['iframeType'] = $cookie->iframeType;
                }

                $arrResponse[ $cookie->id ] = $arrCookie;
            }
        }

        return $arrResponse;
    }

    /**
     * Parse token string and return their as array
     *
     * @param $varToken
     *
     * @return array|null
     */
    public static function parseToken($varToken): ?array
    {
        if(is_array($varToken)){
            return $varToken;
        }

        if($varToken === ''){
            return null;
        }

        if(strpos($varToken, ',') !== false)
        {
              $varToken = explode(",", $varToken);
        }else $varToken = [$varToken];

        return $varToken;
    }

    /**
     * Delete cookie by their token/s
     *
     * @param $varToken
     */
    public static function deleteCookieByToken($varToken): void
    {
        $varToken = static::parseToken($varToken);

        if(null === $varToken){
            return;
        }

        $arrDomains = static::getDomainCollection($_SERVER['HTTP_HOST']);

        foreach ($varToken as $token)
        {
            setcookie(trim($token), "", time() - 3600, '/');

            foreach ($arrDomains as $domain)
            {
                setcookie(trim($token), "", time() - 3600, '/', $domain);
            }
        }
    }

    /**
     * Return a collection of possible domains
     *
     * @param $domain
     *
     * @return array
     */
    private static function getDomainCollection($domain): array
    {
        $arrCollection = [$domain, '.' . $domain];

        preg_match("/[^\.\/]+\.[^\.\/]+$/", $domain, $matches);
        $strDomain = $matches[0];

        // Add domain without subdomains
        $arrCollection[] = $strDomain;

        // Add domain without subdomains and starting dot
        $arrCollection[] = '.' . $strDomain;

        return array_unique($arrCollection);
    }

    /**
     * Deletes the cookie from old versions
     *
     * @deprecated Deprecated since CCB 1.2.0 to be removed in 2.0. Provided for backward compatibility.
     */
    public static function checkCookie(): void
    {
        if(Input::cookie(System::getContainer()->getParameter('contao_cookiebar.storage_key')))
        {
            setcookie(System::getContainer()->getParameter('contao_cookiebar.storage_key'),"",time() - 3600,'/');
        }
    }

    /**
     * Create and save new log entry
     *
     * @param $configId
     * @param $version
     * @param null|string $domain
     * @param null|string $url
     * @param null|string $ip
     * @param null $data
     */
    public static function log($configId, $version, $domain=null, $url=null, $ip=null, $data=null): void
    {
        $objLog = new CookieLogModel();

        $objLog->cid = $configId;
        $objLog->version = $version;
        $objLog->domain = $domain ?? Environment::get('url');
        $objLog->url = $url ?? Environment::get('requestUri');
        $objLog->ip = $ip ?? Environment::get('ip');
        $objLog->tstamp = time();

        if(null !== $data)
        {
            $objResult = Database::getInstance()->prepare("SELECT id, title, token FROM tl_cookie WHERE id IN (" . implode(",", $data) . ")")->execute();
            $objLog->config = serialize($objResult->fetchAllAssoc());
        }

        $objLog->save();
    }
}
