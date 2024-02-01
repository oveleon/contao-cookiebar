<?php

declare(strict_types=1);

namespace Oveleon\ContaoCookiebar\EventListener;

use Contao\ContentModel;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoCookiebar\Cookiebar;
use Oveleon\ContaoCookiebar\Model\CookiebarModel;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class KernelRequestListener
{
    private ?string $rootPageBuffer = null;
    private mixed $rootPagePosition = '';
    private ?PageModel $objPage = null;
    private ?CookiebarModel $cookiebarModel = null;
    private ?string $globalCss = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly TokenChecker        $tokenChecker,
        private readonly ScopeMatcher        $scopeMatcher
    )
    {
    }

    /**
     * @param RequestEvent $event
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (
            $this->rootPageBuffer !== null ||
            $this->scopeMatcher->isFrontendRequest($request) === false ||
            !$request->attributes->has('pageModel')
        )
        {
            return;
        }

        // use first request of any Contao-request to handle the pageModel settings
        // because every Contao-Request does have the PageModel
        // @TODO maybe it can be moved to ResponseListener at first place
        $pageModel = $request->attributes->get('pageModel');
        if ($pageModel instanceof PageModel)
        {
            $this->objPage = $pageModel;

            $rootPageObject = PageModel::findByPk($pageModel->rootId);
            if ($rootPageObject instanceof PageModel)
            {
                $this->prepareCookieBar($pageModel, $rootPageObject);
            }

        }

    }

    /**
     * @param ResponseEvent $event
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (
            $this->scopeMatcher->isFrontendRequest($request) === false ||
            !$request->attributes->has('pageModel')
        )
        {
            return;
        }

        $response = $event->getResponse();
        $content = $response->getContent();

        // normal Website only have one Main-Request for fe_page
        // Ajax-requests do not have a Root-Page so CookieBar do not work
        $pageModel = $request->attributes->get('pageModel');
        if (
            ($pageModel instanceof PageModel) &&
            $this->isPageTemplate($event) === true
        )
        {
            $content = match ($this->rootPagePosition)
            {
                'bodyAboveContent' => preg_replace("/<body([^>]*)>(.*?)<\/body>/is", "<body$1>$this->rootPageBuffer$2</body>", $content),
                default => str_replace("</body>", "$this->rootPageBuffer</body>", $content),
            };

            $content = $this->injectGlobalJs($content);
            $response->setContent($content);

        }

        if ($request->attributes->has('contentModel'))
        {
            $contentModel = $request->attributes->get('contentModel');

            if (!$contentModel instanceof ContentModel)
            {
                $contentModel = ContentModel::findByPk($contentModel);
            }

            // renew $content because using insertTags in modules it could be that contentModel and moduleModel is set
            $content = $this->parseTemplates($contentModel, $content);
            $response->setContent($content);

        }

        if ($request->attributes->has('moduleModel'))
        {
            $moduleModel = $request->attributes->get('moduleModel');

            if (!$moduleModel instanceof ModuleModel)
            {
                $moduleModel = ModuleModel::findByPk($moduleModel);
            }

            // renew $content because using insertTags in modules it could be that contentModel and moduleModel is set
            $content = $this->parseTemplates($moduleModel, $content);
            $response->setContent($content);

        }

    }

    /**
     * @param PageModel $pageModel
     * @param PageModel $rootPageModel
     * @return void
     */
    private function prepareCookieBar(PageModel $pageModel, PageModel $rootPageModel): void
    {
        $objConfig = Cookiebar::getConfigByPage($rootPageModel);

        if (!$objConfig instanceof CookiebarModel)
        {
            return;
        }

        $this->cookiebarModel = $objConfig;

        $strHtml = Cookiebar::parseCookiebarTemplate($objConfig);

        if ((string)$objConfig->scriptPosition === 'body')
        {
            $strHtml .= '<script src="bundles/contaocookiebar/scripts/cookiebar.min.js"></script>';
        } else
        {
            // @TODO better implementation
            $this->globalCss = '<script src="/bundles/contaocookiebar/scripts/cookiebar.min.js?v=' . time() . '"></script>';
            // $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaocookiebar/scripts/cookiebar.min.js|static';
        }

        $strHtml .= vsprintf("<script>var cookiebar = new ContaoCookiebar({configId:%s,pageId:%s,version:%s,lifetime:%s,token:'%s',doNotTrack:%s,currentPageId:%s,excludedPageIds:%s,cookies:%s,configs:%s,disableTracking:%s, texts:{acceptAndDisplay:'%s'}});</script>", [
            $objConfig->id,
            $objConfig->pageId,
            $objConfig->version,
            System::getContainer()->getParameter('contao_cookiebar.lifetime'),
            System::getContainer()->getParameter('contao_cookiebar.storage_key'),
            System::getContainer()->getParameter('contao_cookiebar.consider_dnt') ? 1 : 0,
            $pageModel->id,
            json_encode(StringUtil::deserialize($objConfig->excludePages)),
            json_encode(Cookiebar::validateCookies($objConfig)),
            json_encode(Cookiebar::validateGlobalConfigs($objConfig)),
            $this->tokenChecker->hasBackendUser() && !!$objConfig->disableTrackingWhileLoggedIn ? 1 : 0,
            $this->translator->trans('tl_cookiebar.acceptAndDisplayLabel', [], 'contao_default')
        ]);

        $this->rootPageBuffer = $strHtml;
        $this->rootPagePosition = $objConfig->position;

    }

    /**
     * @param Model $model
     * @param string $buffer
     * @return string
     * s*/
    private function parseTemplates(Model $model, string $buffer): string
    {
        $template = $model->typePrefix . $model->type;

        if (
            null === $this->cookiebarModel ||
            null === $this->objPage
        )
        {
            return $buffer;
        }

        $objConfig = $this->cookiebarModel;

        $arrTypes = Cookiebar::getIframeTypes();
        $arrCookies = Cookiebar::validateCookies($objConfig);

        if (!is_array($arrTypes))
        {
            return $buffer;
        }

        foreach ($arrTypes as $strType => $arrTemplates)
        {
            if (!in_array($template, $arrTemplates))
            {
                continue;
            }

            foreach ($arrCookies as $cookie)
            {
                if (isset($cookie['iframeType']) && $cookie['iframeType'] === $strType)
                {
                    $strBlockUrl = '/cookiebar/block/' . $this->objPage->language . '/' . $cookie['id'] . '?redirect=';

                    // Check if the element is delivered with a preview image
                    if (strpos($buffer, 'id="splashImage') !== false)
                    {
                        // Regex: Modify href attribute for splash images
                        $atagRegex = "/id=\"splashImage_([^>]*)href=\"([^>]*)\"/is";

                        // Get current href attribute
                        preg_match($atagRegex, $buffer, $matches);

                        // Overwrite href attribute
                        $buffer = preg_replace($atagRegex, 'id="splashImage_$1href="' . $strBlockUrl . urlencode($matches[2]) . '"', $buffer);
                        $buffer = str_replace('iframe.src', 'iframe.setAttribute("data-ccb-id", "' . $cookie['id'] . '"); iframe.src', $buffer);

                    } else
                    {
                        // Regex: Modify src attribute for iframes
                        $frameRegex = "/<iframe([\s\S]*?)src=([\\\\\"\']+)(.*?)[\\\\\"\']+/i";

                        // Get current src attribute
                        preg_match_all($frameRegex, $buffer, $matches);

                        $matchCount = count($matches[0]);
                        for ($i = 0; $i < $matchCount; $i++)
                        {

                            $quote = $matches[2][$i];
                            $search = 'src=' . $quote;
                            $replace = 'data-ccb-id=' . $quote . $cookie['id'] . $quote . '  src=' . $quote . $strBlockUrl . urlencode($matches[3][$i]) . $quote . ' data-src=' . $quote;

                            $iframe = str_replace($search, $replace, $matches[0][$i]);
                            $buffer = str_replace($matches[0][$i], $iframe, $buffer);

                        }

                    }

                }

            }

            break;

        }

        return $buffer;

    }

    /**
     * @param ResponseEvent $event
     * @return bool
     * see also Contao\CoreBundle\EventListener\PreviewToolbarListener
     */
    private function isPageTemplate(ResponseEvent $event): bool
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (
            $request->isXmlHttpRequest()
            || (!$response->isSuccessful() && !$response->isClientError())
        )
        {
            return false;
        }

        if (
            'html' !== $request->getRequestFormat()
            || !str_contains((string)$response->headers->get('Content-Type'), 'text/html')
            || false !== stripos((string)$response->headers->get('Content-Disposition'), 'attachment;')
        )
        {
            return false;
        }

        if (false === strripos($response->getContent(), '</body>'))
        {
            return false;
        }

        // @TODO Maybe this can be removed
        if (!$this->scopeMatcher->isFrontendMainRequest($event))
        {
            return false;
        }

        return true;

    }

    /**
     * @param string $content
     * @return string
     * see also Contao\CoreBundle\EventListener\PreviewToolbarListener
     */
    private function injectGlobalJs(string $content): string
    {
        if (is_string($this->globalCss) && $this->globalCss !== '')
        {
            $pos = strripos($content, '</head>');

            if (false !== $pos)
            {
                $content = substr($content, 0, $pos) . "\n" . $this->globalCss . "\n" . substr($content, $pos);
            }

        }

        return $content;

    }

}