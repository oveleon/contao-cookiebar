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

use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;

class Cookiebar
{
    /**
     * Create and return config object
     *
     * @param integer $configId
     * @param null $objMeta
     *
     * @return object
     */
    public static function getConfig(int $configId, $objMeta=null)
    {
        $objCookiebar = CookiebarModel::findById($configId);

        if(null === $objCookiebar)
        {
            return null;
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
            $objConfig->description = $objMeta->cookiebarDescription;
            $objConfig->infoUrls    = $objMeta->cookiebarInfoUrls;
            $objConfig->template    = $objMeta->cookiebarTemplate;
            $objConfig->alignment   = $objMeta->cookiebarAlignment;
            $objConfig->blocking    = $objMeta->cookiebarBlocking;
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
        $objTemplate->groups = $objConfig->groups;

        $objTemplate->saveLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['saveLabel'];
        $objTemplate->acceptAllLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['acceptAllLabel'];
        $objTemplate->denyAllLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['denyAllLabel'];
        $objTemplate->infoLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['infoLabel'];
        $objTemplate->showDetailsLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['showDetailsLabel'];
        $objTemplate->hideDetailsLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['hideDetailsLabel'];
        $objTemplate->providerLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['providerLabel'];
        $objTemplate->expireLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['expireLabel'];

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
     * Collects scripts and deletes unused cookies
     *
     * @param object     $objConfig
     * @param array|null $arrCookies A collection of Cookie-IDs
     * @param bool       $includeObject Also returns the cookie object
     *
     * @return array
     */
    public static function validateCookies($objConfig, ?array $arrCookies=null, bool $includeObject=false): array
    {
        $arrStorage = static::getCookie();
        $arrResponse = [];
        $arrCurrentCookies = [];

        if(isset($arrStorage['cookies']))
        {
            $arrCurrentCookies = $arrStorage['cookies'];
        }

        $arrCookies = $arrCookies === null ? $arrCurrentCookies : $arrCookies;

        foreach ($objConfig->groups as $group)
        {
            foreach ($group->cookies as $cookie)
            {
                if($cookie->isLocked || $cookie->type === 'default')
                {
                    continue;
                }

                if(!\in_array($cookie->id, $arrCookies) && \in_array($cookie->id, $arrCurrentCookies))
                {
                    static::deleteCookieByToken($cookie->token);
                }

                $arrCookie = [
                    'id'        => $cookie->id,
                    'type'      => $cookie->type,
                    'confirmed' => \in_array($cookie->id, $arrCookies),
                    'resources' => $cookie->resources,
                    'scripts'   => $cookie->scripts
                ];

                if($cookie->type === 'iframe')
                {
                    $arrCookie['iframeType'] = $cookie->iframeType;
                }

                if($includeObject)
                {
                    $arrCookie['object'] = $cookie;
                }

                $arrResponse[] = $arrCookie;
            }
        }

        return $arrResponse;
    }

    /**
     * Delete cookie by their token/s
     *
     * @param $varToken
     */
    public static function deleteCookieByToken($varToken): void
    {
        if(strpos($varToken, ',') !== false)
        {
              $varToken = explode(",", $varToken);
        }else $varToken = [$varToken];

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
     * @return array|null
     */
    private static function getDomainCollection($domain): ?array
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
     * Set Cookiebar-Cookie
     *
     * @param $varValue
     */
    public static function setCookie($varValue): void
    {
        setcookie(
            System::getContainer()->getParameter('contao_cookiebar.cookie_token'),
            $varValue,
            time() + System::getContainer()->getParameter('contao_cookiebar.cookie_lifetime'),
            '/'
        );
    }

    /**
     * Returns the currently stored cookie
     *
     * @param bool $decodeJson
     * @param bool $assoc
     *
     * @return string|array|null
     */
    public static function getCookie($decodeJson=true, $assoc=true)
    {
        $strCookie = Input::cookie(System::getContainer()->getParameter('contao_cookiebar.cookie_token'));

        if($decodeJson && $strCookie)
        {
            return json_decode($strCookie, $assoc);
        }

        return $strCookie;
    }

    /**
     * Check whether a cookie was accepted based on the ID or the token
     *
     * @param $varValue Cookie-ID or Token
     * @param null $pageRootId
     *
     * @return bool
     */
    public static function issetCookie($varValue, $pageRootId=null): bool
    {
        // Get cookie by id
        if(is_numeric($varValue))
        {
            $objCookie = CookieModel::findById($varValue);
        }
        // Get cookie by token
        else
        {
            if(null === $pageRootId)
            {
                global $objPage;
                $pageRootId = $objPage->rootId;
            }

            $objCookie = CookieModel::findByToken($varValue, $pageRootId);
        }

        if(null !== $objCookie)
        {
            $arrStorage = static::getCookie();

            if($arrStorage && $arrStorage['cookies'] && in_array($objCookie->id, $arrStorage['cookies']))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Check the browser setting whether cookies are allowed
     *
     * @return bool
     */
    public static function cookiesAllowed(): bool
    {
        if(!System::getContainer()->getParameter('contao_cookiebar.consider_dnt'))
        {
            return true;
        }

        return !(isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1);
    }

    /**
     * Create and save new log entry
     *
     * @param $objConfig
     * @param null|string $domain
     * @param null|string $url
     * @param null|string $ip
     */
    public static function log($objConfig, $domain=null, $url=null, $ip=null): void
    {
        $objLog = new CookieLogModel();

        $objLog->pid = $objConfig->id;
        $objLog->version = $objConfig->version;
        $objLog->domain = $domain ?? Environment::get('url');
        $objLog->url = $url ?? Environment::get('requestUri');
        $objLog->ip = $ip ?? Environment::get('ip');
        $objLog->tstamp = time();
        $objLog->config = serialize(static::validateCookies($objConfig));

        $objLog->save();
    }
}
