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


use Contao\Config;
use Contao\DataContainer;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use FOS\HttpCache\ResponseTagger;
use Oveleon\ContaoCookiebar\Exception\NoCookiebarSpecifiedException;
use Oveleon\ContaoCookiebar\Model\CookiebarModel;
use Oveleon\ContaoCookiebar\Model\GlobalConfigModel;
use Oveleon\ContaoCookiebar\Model\CookieGroupModel;
use Oveleon\ContaoCookiebar\Model\CookieLogModel;
use Oveleon\ContaoCookiebar\Model\CookieModel;
use Symfony\Component\HttpFoundation\IpUtils;

class Cookiebar
{
    /**
     * Config key
     */
    const GLOBAL_CONFIG_KEY = 'ccb_global_config';

    /**
     * Config cache
     */
    static private ?CookiebarModel $configCache = null;

    /**
     * Create and return config object
     */
    public static function getConfig(int $configId, int $pageId, ?PageModel $objMeta=null): ?CookiebarModel
    {
        if(null !== static::$configCache)
        {
            return static::$configCache;
        }

        if(null === $objCookiebar = CookiebarModel::findById($configId))
        {
            throw new NoCookiebarSpecifiedException('No cookiebar specified');
        }

        if(null === $objCookieGroups = CookieGroupModel::findPublishedByPid($objCookiebar->id))
        {
            return null;
        }

        $objConfig = $objCookiebar->current();
        $arrGroups = [];

        // Overwrite metadata
        if(null !== $objMeta)
        {
            $objConfig->description       = $objMeta->cookiebarDescription;
            $objConfig->infoDescription   = $objMeta->cookiebarInfoDescription;
            $objConfig->infoUrls          = $objMeta->cookiebarInfoUrls;
            $objConfig->excludePages      = $objMeta->cookiebarExcludePages;
            $objConfig->buttonColorScheme = $objMeta->cookiebarButtonColorScheme;
            $objConfig->template          = $objMeta->cookiebarTemplate;
            $objConfig->alignment         = $objMeta->cookiebarAlignment;
            $objConfig->blocking          = $objMeta->cookiebarBlocking;
        }

        DataContainer::loadDataContainer('tl_cookie');

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

                    // Adding the global configuration with checking whether this may be used
                    $strTypePalette = $GLOBALS['TL_DCA']['tl_cookie']['palettes'][$objCookies->type] ?? false;

                    if($objCookies->globalConfig && $strTypePalette && str_contains($strTypePalette, 'globalConfig'))
                    {
                        $intConfigKey = $objCookies->globalConfig;
                        $arrConfigs   = Config::has(self::GLOBAL_CONFIG_KEY) ? Config::get(self::GLOBAL_CONFIG_KEY) : null;

                        if(null === $arrConfigs || !array_key_exists($intConfigKey, $arrConfigs))
                        {
                            /** @var GlobalConfigModel $objConfigModel */
                            $objConfigModel = GlobalConfigModel::findById($intConfigKey);

                            if(null !== $objConfigModel)
                            {
                                $objGlobalConfig = new GlobalConfig($objConfigModel);
                                $objGlobalConfig->addCookie( $objCookies->current() );

                                $arrConfigs[ $intConfigKey ] = $objGlobalConfig;

                                Config::set(self::GLOBAL_CONFIG_KEY, $arrConfigs);
                            }
                        }
                        else
                        {
                            /** @var GlobalConfig $objGlobalConfig */
                            $objGlobalConfig = $arrConfigs[ $intConfigKey ];
                            $objGlobalConfig->addCookie( $objCookies->current() );

                            $arrConfigs[ $intConfigKey ] = $objGlobalConfig;

                            Config::set(self::GLOBAL_CONFIG_KEY, $arrConfigs);
                        }
                    }

                    $arrCookies[] = new Cookie($objCookies->current());
                }
            }

            $objGroup->hasCookies = count($arrCookies);
            $objGroup->isLocked = $objGroup->identifier === 'lock';
            $objGroup->cookies = $arrCookies;

            $arrGroups[] = $objGroup;
        }

        $objConfig->groups = $arrGroups;
        $objConfig->pageId = $pageId;
        $objConfig->configs = null;

        // Add global configuration
        if(Config::has(self::GLOBAL_CONFIG_KEY))
        {
            $objConfig->configs = Config::get(self::GLOBAL_CONFIG_KEY);
        }

        // Cache config
        static::$configCache = $objConfig;

        return $objConfig;
    }

    /**
     * Return config by page
     */
    public static function getConfigByPage(int|PageModel $varPage): ?CookiebarModel
    {
        if(!($varPage instanceof PageModel))
        {
              $objPage = PageModel::findById( $varPage );
        }else $objPage = $varPage;

        if(!$objPage->activateCookiebar)
        {
            return null;
        }

        return static::getConfig((int) $objPage->cookiebarConfig, $objPage->id, !!$objPage->overwriteCookiebarMeta ? $objPage : null);
    }

    /**
     * Returns all configurations ([id => title])
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
     */
    public static function getIframeTypes(): ?array
    {
        return System::getContainer()->getParameter('contao_cookiebar.iframe_types');
    }

    /**
     * Parse Cookiebar template
     */
    public static function parseCookiebarTemplate(CookiebarModel $objConfig): string
    {
        System::loadLanguageFile('tl_cookiebar');

        $objTemplate = new FrontendTemplate($objConfig->template);

        $cssID = StringUtil::deserialize($objConfig->cssID, true) + [null, null];
        $objTemplate->cssID = $cssID[0];
        $objTemplate->class = $cssID[1] ? $objConfig->template . ' ' . $objConfig->alignment . ' ' . trim($cssID[1]) : $objConfig->template . ' ' . $objConfig->alignment;

        if($objConfig->blocking)
        {
            $objTemplate->class .= ' cc-blocked';
        }

        $objTemplate->description = $objConfig->description;
        $objTemplate->buttonColorScheme = $objConfig->buttonColorScheme;
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
        $objTemplate->tokenLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['tokenLabel'];

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

        // Tag the response
        if (System::getContainer()->has('fos_http_cache.http.symfony_response_tagger'))
        {
            /** @var ResponseTagger $responseTagger */
            $responseTagger = System::getContainer()->get('fos_http_cache.http.symfony_response_tagger');
            $responseTagger->addTags(array('oveleon.cookiebar.' . $objConfig->id));
        }

        return $objTemplate->parse();
    }

    /**
     * Collect cookie scripts
     */
    public static function validateCookies(CookiebarModel $objConfig): array
    {
        $arrResponse = [];

        foreach ($objConfig->groups as $group)
        {
            foreach ($group->cookies as $cookie)
            {
                if($cookie->isLocked)
                {
                    continue;
                }

                $arrCookie = [
                    'id'        => $cookie->id,
                    'type'      => $cookie->type,
                    'checked'   => !!$cookie->checked,
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
     * Collect global config scripts
     */
    public static function validateGlobalConfigs(CookiebarModel $objConfig): array
    {
        $arrResponse = [];

        if(null === $objConfig->configs)
        {
            return $arrResponse;
        }

        foreach ($objConfig->configs as $config)
        {
            $arrConfig = [
                'id'        => $config->id,
                'type'      => $config->type,
                'cookies'   => array_combine(array_keys($config->arrCookies), array_keys($config->arrCookies)),
                'resources' => $config->resources,
                'scripts'   => $config->scripts
            ];

            $arrResponse[ $config->id ] = $arrConfig;
        }

        return $arrResponse;
    }

    /**
     * Parse token string and return their as array
     */
    public static function parseToken(array|string $varToken): ?array
    {
        if(is_array($varToken))
        {
            return $varToken;
        }

        if($varToken === ''){
            return null;
        }

        if(str_contains($varToken, ','))
        {
              $varToken = explode(",", $varToken);
        }else $varToken = [$varToken];

        return $varToken;
    }

    /**
     * Delete cookie by their tokens
     */
    public static function deleteCookieByToken(array|string $varToken): void
    {
        $varToken = static::parseToken($varToken);

        if(null === $varToken)
        {
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
     */
    private static function getDomainCollection(string $domain): array
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
     * Create and save new log entry
     */
    public static function log(int $configId, ?string $url=null, ?string $ip=null, ?array $data=null): void
    {
        $strIp = $ip ?? Environment::get('ip');

        if(System::getContainer()->getParameter('contao_cookiebar.anonymize_ip'))
        {
            $strIp = IpUtils::anonymize($strIp);
        }

        // Check if the cookie bar exists (#128)
        if(!$cookieBar = CookiebarModel::findById($configId))
        {
            throw new \InvalidArgumentException("Cookie bar configuration could not be found, the log entry was skipped");
        }

        // Check if it is a valid URL (#128)
        if ($url && (filter_var('https://www.example.com' . $url, FILTER_VALIDATE_URL) === false))
        {
            throw new \InvalidArgumentException("The URL passed is not valid");
        }

        $objLog = new CookieLogModel();
        $objLog->cid = $configId;
        $objLog->version = $cookieBar->version;
        $objLog->domain = Environment::get('url');
        $objLog->url = $url ?? Environment::get('requestUri');
        $objLog->ip = $strIp;
        $objLog->tstamp = time();

        if(null !== $data)
        {
            // Remove values which are not of type integer (#128)
            foreach ($data as $index => $cookieId)
            {
                if(!((int)$cookieId == $cookieId))
                {
                    unset($data[$index]);
                }
            }

            /** @var Connection $db */
            $db = System::getContainer()->get('database_connection');
            $result = $db->fetchAllAssociative("SELECT id, title, token FROM tl_cookie WHERE id IN (?)", [$data], [Connection::PARAM_INT_ARRAY]);
            $objLog->config = serialize($result);
        }

        $objLog->save();
    }
}
