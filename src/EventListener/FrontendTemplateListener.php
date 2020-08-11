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

use Contao\System;
use Oveleon\ContaoCookiebar\Cookiebar;

class FrontendTemplateListener
{
    /**
     * Output front end template
     *
     * @param string $buffer
     * @param string $template
     *
     * @return string
     */
    public function onOutputFrontendTemplate(string $buffer, string $template): string
    {
        $arrPageTemplates = System::getContainer()->getParameter('contao_cookiebar.page_templates') ?? ['fe_page'];

        if (in_array($template, $arrPageTemplates))
        {
            global $objPage;

            $objConfig = Cookiebar::getConfigByPage($objPage->rootId);

            if(null === $objConfig)
            {
                return $buffer;
            }

            // If a cookie is still set by an older version, it must be deleted
            Cookiebar::checkCookie();

            // Parse template
            $strHtml = Cookiebar::parseCookiebarTemplate($objConfig);

            // Load cookie bar scripts
            if($objConfig->scriptPosition === 'body')
            {
                $strHtml .= '<script src="bundles/contaocookiebar/scripts/cookiebar.min.js"></script>';
            }
            else
            {
                $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaocookiebar/scripts/cookiebar.min.js';
            }

            // Add cookiebar script initialization
            $strHtml .= sprintf("<script>var cookiebar = new ContaoCookiebar({configId:%s,pageId:%s,version:%s,token:'%s',doNotTrack:%s,cookies:%s});</script>",
                $objConfig->id,
                $objConfig->pageId,
                $objConfig->version,
                System::getContainer()->getParameter('contao_cookiebar.storage_key'),
                Cookiebar::cookiesAllowed() ? 0 : 1,
                json_encode(Cookiebar::validateCookies($objConfig))
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
        }

        return $buffer;
    }

    /**
     * Parse front end template
     *
     * @param string $buffer
     * @param string $template
     *
     * @return string
     */
    public function onParseFrontendTemplate(string $buffer, string $template): string
    {
        global $objPage;

        if (TL_MODE == 'BE' || null === $objPage)
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
                        $strBlockUrl = '/cookiebar/block/'.$cookie['id'].'?redirect=';

                        // Check if the element is delivered with a preview image
                        if(strpos($buffer, 'id="splashImage') !== false)
                        {
                            // Regex: Modify href attribute for splash images
                            $atagRegex = "/id=\"splashImage_([^>]*)href=\"([^>]*)\"/is";

                            // Get current href attribute
                            preg_match($atagRegex, $buffer, $matches);

                            // Overwrite href attribute
                            $buffer = preg_replace($atagRegex, 'id="splashImage_$1href="'.$strBlockUrl.urlencode($matches[2]).'"', $buffer);
                        }
                        else
                        {
                            // Regex: Modify src attribute for iframes
                            $frameRegex = "/<iframe(.*?s*)src=\"(.*?)\"/is";

                            // Get current src attribute
                            preg_match($frameRegex, $buffer, $matches);

                            // Overwrite src attribute
                            $buffer = preg_replace($frameRegex, '<iframe$1data-ccb-id="'.$cookie['id'].'" src="'.$strBlockUrl.urlencode($matches[2]).'"', $buffer);
                        }
                    }
                }

                break;
            }
        }

        return $buffer;
    }
}
