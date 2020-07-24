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
        if ('fe_page' === $template && Cookiebar::cookiesAllowed())
        {
            global $objPage;

            $objConfig = Cookiebar::getConfigByPage($objPage->rootId);

            if(!Cookiebar::getCookie())
            {
                Cookiebar::setCookie(json_encode([
                    'configId' => $objConfig->id,
                    'pageId'   => $objPage->rootId,
                    'version'  => -1
                ]));
            }

            if(null === $objConfig)
            {
                return $buffer;
            }

            $strHtml = Cookiebar::parseCookiebarTemplate($objConfig);
            $arrCookies = Cookiebar::validateCookies($objConfig);

            // Add cookiebar script
            $strHtml .= sprintf("<script>var cookiebar = new ContaoCookiebar({configId:%s,pageId:%s,version:%s,token:'%s',cookies:%s});</script>",
                $objConfig->id,
                $objConfig->pageId,
                $objConfig->version,
                System::getContainer()->getParameter('contao_cookiebar.cookie_token'),
                json_encode($arrCookies)
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

        $arrTypes = Cookiebar::getIframeTypes();
        $objConfig = Cookiebar::getConfigByPage($objPage->rootId);
        $arrCookies = Cookiebar::validateCookies($objConfig);

        foreach ($arrTypes as $strType => $arrTemplates)
        {
            if(in_array($template, $arrTemplates))
            {
                foreach ($arrCookies as $cookie)
                {
                    if(isset($cookie['iframeType']) && $cookie['iframeType'] === $strType && !$cookie['confirmed'])
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
                            $buffer = preg_replace($frameRegex, '<iframe$1src="'.$strBlockUrl.urlencode($matches[2]).'"', $buffer);
                        }
                    }
                }

                break;
            }
        }

        return $buffer;
    }
}
