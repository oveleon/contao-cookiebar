<?php

namespace Oveleon\ContaoCookiebar\EventListener\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Image;
use Contao\Message;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Oveleon\ContaoCookiebar\Model\GlobalConfigModel;
use Oveleon\ContaoCookiebar\Model\CookieGroupModel;
use Oveleon\ContaoCookiebar\Model\CookieModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class CookieCallbackListener
{
    use CookiebarTrait;

    public function __construct(
        private readonly RequestStack        $requestStack,
        private readonly Connection          $connection,
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Handle multiple edit
     *
     * @Callback(table="tl_cookie", target="config.onload")
     *
     * @throws Exception
     */
    public function handleMultipleEdit(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = $this->requestStack->getSession();

        switch ($action = $request->get('act'))
        {
            case 'deleteAll':
            case 'copyAll':
            case 'cutAll':

                $sessionFields = $session->all();

                $currentIds = $action == 'deleteAll' ? $sessionFields['CURRENT']['IDS'] : $sessionFields['CLIPBOARD']['tl_cookie']['id'];

                // Set allowed cookie IDs (delete multiple)
                if (is_array($currentIds))
                {
                    $arrIds = [];

                    foreach ($currentIds as $id)
                    {
                        $objCookies = $this->connection->fetchOne("SELECT id, pid, identifier FROM tl_cookie WHERE id=?", [$id]);

                        if ($objCookies->numRows < 1)
                        {
                            continue;
                        }

                        // Locked groups cannot be deleted or copied
                        if ($objCookies->identifier !== 'lock')
                        {
                            $arrIds[] = $id;
                        }
                    }

                    if($action == 'deleteAll')
                    {
                        $sessionFields['CURRENT']['IDS'] = $arrIds;
                    }
                    else
                    {
                        if(empty($arrIds))
                        {
                            $sessionFields['CLIPBOARD']['tl_cookie'] = $arrIds;
                        }
                        else
                        {
                            $sessionFields['CLIPBOARD']['tl_cookie']['id'] = $arrIds;
                        }
                    }
                }

                // Overwrite session
                $session->replace($sessionFields);
        }
    }

    /**
     * Adjust locked DCA's (necessary cookies)
     *
     * @Callback(table="tl_cookie", target="config.onload")
     */
    public function handleLockedDca(DataContainer $dc): void
    {
        $objCookie = CookieModel::findById($dc->id);

        if($objCookie)
        {
            $objGroup = CookieGroupModel::findById($objCookie->pid);

            if($objCookie->identifier === 'lock' || $objGroup->identifier === 'lock')
            {
                $GLOBALS['TL_DCA']['tl_cookie']['palettes']['default'] = str_replace(',type', '', $GLOBALS['TL_DCA']['tl_cookie']['palettes']['default']);
            }
        }
    }

    /**
     * Customize a cookie list item
     *
     * @Callback(table="tl_cookie", target="list.sorting.child_record")
     */
    public function listCookieItem(array $arrRow): string
    {
        $vendorIdAdditionFields = [
            'facebookPixel',
            'googleAnalytics',
            'matomoTagManager',
            'matomo'
        ];

        $configAdditionFields = [
            'script',
            'template',
            'googleConsentMode'
        ];

        $getConfigTitle = static function($id): string
        {
            if(null !== $objConfig = GlobalConfigModel::findById($id))
            {
                return $objConfig->title;
            }

            return '';
        };

        $add = match (true)
        {
            $arrRow['vendorId'] && \in_array($arrRow['type'], $vendorIdAdditionFields) => $arrRow['vendorId'],
            $arrRow['globalConfig'] && \in_array($arrRow['type'], $configAdditionFields) => $getConfigTitle($arrRow['globalConfig']),
            default => ''
        };

        return vsprintf('<div class="tl_content_left">%s <span style="color:#999;padding-left:3px">[%s%s]</span></div>', [
            $arrRow['title'],
            $GLOBALS['TL_LANG']['tl_cookie'][$arrRow['type']][0],
            $add ?  ' | <span style="color:#f47c00;">' . $add . '</span>' : ''
        ]);
    }

    /**
     * Disable locked fields
     *
     * @Callback(table="tl_cookie", target="fields.disabled.load")
     * @Callback(table="tl_cookie", target="fields.token.load")
     */
    public function disableLockedField(mixed $varValue, DataContainer $dc): string
    {
        if($dc->activeRecord->identifier === 'lock')
        {
            $GLOBALS['TL_DCA']['tl_cookie']['fields'][ $dc->field ]['eval']['disabled'] = true;
        }

        return $varValue;
    }

    /**
     * Check if a field need to be mandatory
     *
     * @Callback(table="tl_cookie", target="fields.token.load")
     */
    public function requireField(mixed $varValue, DataContainer $dc): string
    {
        $disableRequire = [
            'default',
            'script',
            'template',
            'iframe',
            'matomo',
            'matomoTagManager',
            'googleConsentMode'
        ];

        if(in_array($dc->activeRecord->type, $disableRequire))
        {
            $GLOBALS['TL_DCA']['tl_cookie']['fields'][ $dc->field ]['eval']['mandatory'] = false;
        }

        return $varValue;
    }

    /**
     * Remove white spaces from tokens
     *
     * @Callback(table="tl_cookie", target="fields.token.save")
     */
    public function cleanupToken(mixed $varValue, DataContainer $dc): string
    {
        return str_replace(" ", "", $varValue);
    }

    /**
     * @Callback(table="tl_cookie", target="fields.token.xlabel")
     */
    public function selectTokenPreset(DataContainer $dc): string
    {
        $id = 'token' . $dc->activeRecord->type;

        return vsprintf(' <a href="javascript:;" id="%s" title="%s" onclick="Backend.getScrollOffset();var token=Cookiebar.getToken(\'%s\',\'%s\');if(token)document.getElementById(\'ctrl_%s\').value=token">%s</a><script>Cookiebar.issetToken(\'%s\',document.getElementById(\'%s\'));</script>', [
            $id,
            $GLOBALS['TL_LANG']['tl_cookie']['tokenConfig_xlabel'],
            $dc->activeRecord->type,
            $GLOBALS['TL_LANG']['tl_cookie']['tokenConfig_'.$dc->activeRecord->type.'_error'] ?? '',
            $dc->field,
            Image::getHtml('theme_import.svg', $GLOBALS['TL_LANG']['tl_cookie']['tokenConfig_xlabel']),
            $dc->activeRecord->type,
            $id
        ]);
    }

    /**
     * @Callback(table="tl_cookie", target="fields.scriptConfig.xlabel")
     */
    public function selectScriptPreset(DataContainer $dc): string
    {
        $id = 'script' . $dc->activeRecord->type;

        $xlabel = vsprintf(' <a href="javascript:;" id="script_%s" title="%s" onclick="Backend.getScrollOffset();ace.edit(\'ctrl_%s_div\').setValue(Cookiebar.getConfig(\'%s\'))">%s</a><script>Cookiebar.issetConfig(\'%s\',document.getElementById(\'script_%s\'));</script>',[
            $id,
            $this->translator->trans('tl_cookie.scriptConfig_xlabel', [], 'contao_default'),
            $dc->field,
            $dc->activeRecord->type,
            Image::getHtml('theme_import.svg', $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel']),
            $dc->activeRecord->type,
            $id
        ]);

        $xlabel .= vsprintf(' <a href="javascript:;" id="docs_%s" title="%s" onclick="Backend.getScrollOffset();window.open(Cookiebar.getDocs(\'%s\'), \'_blank\')">%s</a><script>Cookiebar.issetDocs(\'%s\',document.getElementById(\'docs_%s\'));</script>', [
            $id,
            $this->translator->trans('tl_cookie.scriptDocs_xlabel', [], 'contao_default'),
            $dc->activeRecord->type,
            Image::getHtml('show.svg', $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel']),
            $dc->activeRecord->type,
            $id
        ]);

        return $xlabel;
    }

    /**
     * Add info messages for cookies accept multiple usage
     *
     * @Callback(table="tl_cookie", target="fields.type.load")
     */
    public function addTypeMessage(mixed $varValue, DataContainer $dc): string
    {
        if($varValue === 'googleConsentMode')
        {
            Message::addInfo($GLOBALS['TL_LANG']['tl_cookie']['msgGoogleConsentMode']);
        }

        return $varValue;
    }

    /**
     * Return all iframe types
     *
     * @Callback(table="tl_cookie", target="fields.iframeType.options")
     */
    public function getIframeTypes(): array
    {
        $arrTypes = System::getContainer()->getParameter('contao_cookiebar.iframe_types');
        return array_keys($arrTypes);
    }

    /**
     * Return all block templates
     *
     * @Callback(table="tl_cookie", target="fields.blockTemplate.options")
     */
    public function getBlockTemplates(): array
    {
        return Controller::getTemplateGroup('ccb_element_');
    }

    /**
     * Return all analytic templates
     *
     * @Callback(table="tl_cookie", target="fields.scriptTemplate.options")
     */
    public function getAnalyticTemplates(): array
    {
        return Controller::getTemplateGroup('analytics_');
    }

    /**
     * Makes the global config field mandatory for google consent mode
     *
     * @Callback(table="tl_cookie", target="fields.globalConfig.load")
     */
    public function requireConsentMode(mixed $varValue, DataContainer $dc)
    {
        if($dc->activeRecord->type === 'googleConsentMode')
        {
            $GLOBALS['TL_DCA']['tl_cookie']['fields'][ $dc->field ]['eval']['mandatory'] = true;
        }

        return $varValue;
    }

    /**
     * Overwrite vendor* field translation by cookie type
     *
     * @Callback(table="tl_cookie", target="fields.vendorId.load")
     * @Callback(table="tl_cookie", target="fields.vendorUrl.load")
     */
    public function overwriteTranslation(string $value, DataContainer $dc): string
    {
        return $this->setVendorTranslation($value, $dc);
    }

    /**
     * Check if a button needs to be disabled
     *
     * @Callback(table="tl_cookie", target="list.operations.copy.button")
     * @Callback(table="tl_cookie", target="list.operations.cut.button")
     * @Callback(table="tl_cookie", target="list.operations.delete.button")
     */
    public function disableButton(array $row, ?string $href, string $label, string $title, ?string $icon, string $attributes): string
    {
        return $this->disableButtonOnLocked($row, $href, $label, $title, $icon, $attributes);
    }

    /**
     * Adds a host prefix if none was specified
     *
     * @Callback(table="tl_cookie", target="fields.sourceUrl.save")
     */
    public function addHostPrefix(string $varValue): string
    {
        return $this->setHostPrefix($varValue);
    }
}
