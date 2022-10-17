<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\EventListener;

use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoCookiebar\Cookiebar;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontendTemplateListener
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Output front end template
     */
    #[AsHook('outputFrontendTemplate')]
    public function onOutputFrontendTemplate(string $buffer, string $template): string
    {
        $arrPageTemplates = System::getContainer()->getParameter('contao_cookiebar.page_templates') ?? ['fe_page'];

        if (!in_array($template, $arrPageTemplates) && !str_starts_with($template, 'fe_page'))
        {
            return $buffer;
        }

        global $objPage;

        $objConfig = Cookiebar::getConfigByPage($objPage->rootId);

        if(null === $objConfig)
        {
            return $buffer;
        }

        // Parse template
        $strHtml = Cookiebar::parseCookiebarTemplate($objConfig);

        // Load cookie bar scripts
        if($objConfig->scriptPosition === 'body')
        {
            $strHtml .= '<script src="bundles/contaocookiebar/scripts/cookiebar.min.js"></script>';
        }
        else
        {
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaocookiebar/scripts/cookiebar.min.js|static';
        }

        // Add cookiebar script initialization
        $strHtml .= sprintf("<script>var cookiebar = new ContaoCookiebar({configId:%s,pageId:%s,version:%s,lifetime:%s,token:'%s',doNotTrack:%s,currentPageId:%s,excludedPageIds:%s,cookies:%s,configs:%s,texts:{acceptAndDisplay:'%s'}});</script>",
            $objConfig->id,
            $objConfig->pageId,
            $objConfig->version,
            System::getContainer()->getParameter('contao_cookiebar.lifetime'),
            System::getContainer()->getParameter('contao_cookiebar.storage_key'),
            System::getContainer()->getParameter('contao_cookiebar.consider_dnt') ? 1 : 0,
            $objPage->id,
            json_encode(StringUtil::deserialize($objConfig->excludePages)),
            json_encode(Cookiebar::validateCookies($objConfig)),
            json_encode(Cookiebar::validateGlobalConfigs($objConfig)),
            $this->translator->trans('tl_cookiebar.acceptAndDisplayLabel', [], 'contao_default')
        );

        if(null !== $objConfig)
        {
            switch($objConfig->position)
            {
                case 'bodyAboveContent':
                    $buffer = preg_replace("/<body([^>]*)>(.*?)<\/body>/is", "<body$1>$strHtml$2</body>", $buffer);
                    break;
                default:
                    $buffer = str_replace("</body>", "$strHtml</body>", $buffer);
            }
        }

        return $buffer;
    }


    /**
     * Check content elements to be modified
     */
    #[AsHook('getContentElement')]
    #[AsHook('getFrontendModule')]
    public function parseTemplates(Model $model, string $buffer): string
    {
        global $objPage;

        $template = $model->typePrefix . $model->type;
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request) || null === $objPage)
        {
            return $buffer;
        }

        $objConfig = Cookiebar::getConfigByPage($objPage->rootId);

        if(null === $objConfig)
        {
            return $buffer;
        }

        $arrTypes = Cookiebar::getIframeTypes();
        $arrCookies = Cookiebar::validateCookies($objConfig);

        foreach ($arrTypes as $strType => $arrTemplates)
        {
            if(in_array($template, $arrTemplates))
            {
                foreach ($arrCookies as $cookie)
                {
                    if(isset($cookie['iframeType']) && $cookie['iframeType'] === $strType)
                    {
                        $strBlockUrl = '/cookiebar/block/'.$objPage->language.'/'.$cookie['id'].'?redirect=';

                        // Check if the element is delivered with a preview image
                        if(strpos($buffer, 'id="splashImage') !== false)
                        {
                            // Regex: Modify href attribute for splash images
                            $atagRegex = "/id=\"splashImage_([^>]*)href=\"([^>]*)\"/is";

                            // Get current href attribute
                            preg_match($atagRegex, $buffer, $matches);

                            // Overwrite href attribute
                            $buffer = preg_replace($atagRegex, 'id="splashImage_$1href="'.$strBlockUrl.urlencode($matches[2]).'"', $buffer);
                            $buffer = str_replace('iframe.src', 'iframe.setAttribute("data-ccb-id", "'.$cookie['id'].'"); iframe.src', $buffer);
                        }
                        else
                        {
                            // Regex: Modify src attribute for iframes
                            $frameRegex = "/<iframe([\s\S]*?)src=([\\\\\"\']+)(.*?)[\\\\\"\']+/i";

                            // Get current src attribute
                            preg_match_all($frameRegex, $buffer, $matches);

                            for ($i=0; $i < count($matches[0]); $i++)
                            {
                                $quote   = $matches[2][$i];
                                $search  = 'src=' . $quote;
                                $replace = 'data-ccb-id=' . $quote . $cookie['id'] . $quote . '  src='. $quote . $strBlockUrl . urlencode($matches[3][$i]) . $quote . ' data-src=' . $quote;

                                $iframe = str_replace($search, $replace, $matches[0][$i]);
                                $buffer = str_replace($matches[0][$i], $iframe, $buffer);
                            }
                        }
                    }
                }

                break;
            }
        }

        return $buffer;
    }
}
