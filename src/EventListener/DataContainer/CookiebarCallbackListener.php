<?php

namespace Oveleon\ContaoCookiebar\EventListener\DataContainer;

use Contao\Controller;
use Contao\DataContainer;
use Contao\Message;
use Contao\System;
use Doctrine\DBAL\Connection;
use FOS\HttpCacheBundle\CacheManager;
use Oveleon\ContaoCookiebar\Model\CookieGroupModel;
use Oveleon\ContaoCookiebar\Model\CookieModel;
use Symfony\Contracts\Translation\TranslatorInterface;
use Contao\CoreBundle\ServiceAnnotation\Callback;

class CookiebarCallbackListener
{
    public function __construct(
        private readonly Connection          $connection,
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Create essential group and cookies
     *
     * @Callback(table="tl_cookiebar", target="config.onsubmit")
     */
    public function createEssentialGroupAndCookies(DataContainer $dc)
    {
        $strLang = $dc->activeRecord->essentialCookieLanguage;

        if(!$strLang || $this->hasEssentialGroup($dc))
        {
            return;
        }

        $this->translator->setLocale($strLang);

        $essentialGroup = new CookieGroupModel();
        $essentialGroup->title = $this->translator->trans('tl_cookiebar.defaultEssentialGroupName', [], 'contao_tl_cookiebar');
        $essentialGroup->pid = $dc->id;
        $essentialGroup->identifier = 'lock';
        $essentialGroup->published = 1;
        $essentialGroup->tstamp = time();
        $essentialGroup->save();

        $arrDefaultCookies = [
            [
                'Contao CSRF Token',
                'csrf_contao_csrf_token',
                $this->translator->trans('tl_cookiebar.noExpireTime', [], 'contao_tl_cookiebar'),
                $this->translator->trans('tl_cookiebar.defaultCsrfDescription', [], 'contao_tl_cookiebar'),
                'lock'
            ],
            [
                'Contao HTTPS CSRF Token',
                'csrf_https-contao_csrf_token',
                $this->translator->trans('tl_cookiebar.noExpireTime', [], 'contao_tl_cookiebar'),
                $this->translator->trans('tl_cookiebar.defaultHttpsCsrfDescription', [], 'contao_tl_cookiebar'),
                'lock'
            ],
            [
                'PHP SESSION ID',
                'PHPSESSID',
                $this->translator->trans('tl_cookiebar.noExpireTime', [], 'contao_tl_cookiebar'),
                $this->translator->trans('tl_cookiebar.defaultPhpSessionDescription', [], 'contao_tl_cookiebar'),
                'lock'
            ]
        ];

        foreach ($arrDefaultCookies as $arrCookie)
        {
            $newCookie = new CookieModel();
            $newCookie->pid = $essentialGroup->id;
            $newCookie->title = $arrCookie[0];
            $newCookie->type = 'default';
            $newCookie->token = $arrCookie[1];
            $newCookie->expireTime = $arrCookie[2];
            $newCookie->description = $arrCookie[3];
            $newCookie->identifier = $arrCookie[4];
            $newCookie->published = 1;
            $newCookie->tstamp = time();
            $newCookie->save();
        }

        $this->translator->setLocale($GLOBALS['TL_LANGUAGE']);
    }

    /**
     * Return all cookiebar templates
     *
     * @Callback(table="tl_cookiebar", target="fields.template.options")
     */
    public function getCookiebarTemplates(): array
    {
        return Controller::getTemplateGroup('cookiebar_');
    }

    /**
     * Return all cookiebar templates
     *
     * @Callback(table="tl_cookiebar", target="fields.essentialCookieLanguage.options")
     */
    public function loadAvailableLanguages(): array
    {
        $validLanguages = System::getContainer()->get('contao.intl.locales')->getLocales(null, true);
        $arrLanguages = ['en', 'de', 'sv'];
        $arrReturn = [];

        foreach ($arrLanguages as $strLanguage) {
            $arrReturn[ $strLanguage ] = $validLanguages[ $strLanguage ] ?? $strLanguage;
        }

        return $arrReturn;
    }

    /**
     * Update version
     *
     * @Callback(table="tl_cookiebar", target="fields.updateVersion.save")
     */
    public function updateVersion(string $value, DataContainer $dc): string
    {
        if($value)
        {
            $newVersion = ++$dc->activeRecord->version;

            // Update the database
            $this->connection->update("tl_cookiebar", ['version' => $newVersion], ['id' => $dc->activeRecord->id]);

            /** @var CacheManager $cacheManager */
            $cacheManager = System::getContainer()->get('fos_http_cache.cache_manager');
            $cacheManager->invalidateTags(array('oveleon.cookiebar.' . $dc->activeRecord->id));
        }

        return '';
    }

    /**
     * Checks if the consent log is activated / deactivated
     *
     * @Callback(table="tl_cookiebar", target="config.onload")
     */
    public function showConsentLogInformation(): void
    {
        $container = System::getContainer();

        $consentLog = $container->getParameter('contao_cookiebar.consent_log');
        $anonymizeIp =  $container->getParameter('contao_cookiebar.anonymize_ip');

        Message::addInfo($this->translator->trans('tl_cookiebar.consentLog.'.($consentLog ? 1 : 0), [], 'contao_tl_cookiebar'));

        if ($consentLog && !$anonymizeIp)
        {
            Message::addInfo($this->translator->trans('tl_cookiebar.ipAnonymization', [], 'contao_tl_cookiebar'));
        }
    }

    /**
     * Check if essential groups are allowed to be created
     *
     * @Callback(table="tl_cookiebar", target="config.onload")
     */
    public function checkEssentialGroup(DataContainer $dc)
    {
        if($this->hasEssentialGroup($dc))
        {
            $GLOBALS['TL_DCA']['tl_cookiebar']['fields']['essentialCookieLanguage']['eval']['disabled'] = true;
        }
    }

    /**
     * Check if an essential group exists
     */
    private function hasEssentialGroup(DataContainer $dc): bool
    {
        $countEssentialGroup = CookieGroupModel::countBy(['pid=?', 'identifier=?'], [$dc->id, 'lock']);
        return null !== $countEssentialGroup && $countEssentialGroup >= 1;
    }
}
