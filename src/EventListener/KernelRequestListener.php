<?php

declare(strict_types=1);

namespace Oveleon\ContaoCookiebar\EventListener;

use Contao\ContentModel;
use Contao\ContentProxy;
use Contao\CoreBundle\Csp\CspParser;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Contao\ModuleModel;
use Contao\ModuleProxy;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoCookiebar\Cookiebar;
use Oveleon\ContaoCookiebar\Model\CookiebarModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class KernelRequestListener
{
    private array $rootPageBuffer = [];
    private ?PageModel $objRootPage = null;
    private ?PageModel $objPage = null;
    private ?CookiebarModel $cookiebarModel = null;
    private ?string $globalJavaScript = null;

    private ?array $types = null;
    private array $cookies = [];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly TokenChecker        $tokenChecker,
        private readonly ScopeMatcher        $scopeMatcher,
        private readonly CspParser           $cspParser,
        private readonly int                 $lifetime,
        private readonly bool                $consentLog,
        private readonly string              $storageKey,
        private readonly bool                $considerDnt
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

        // Also handles CookieBar not being activated within rootPage
        if (
            $this->objRootPage instanceof PageModel ||
            !$this->scopeMatcher->isFrontendRequest($request) ||
            !$request->attributes->has('pageModel')
        )
        {
            return;
        }

        $pageModel = $request->attributes->get('pageModel');

        if ($pageModel instanceof PageModel)
        {
            $rootPageObject = PageModel::findByPk($pageModel->rootId);

            if ($rootPageObject instanceof PageModel)
            {
                $this->objRootPage = $rootPageObject;
                $this->objPage = $pageModel;

                $this->prepareCookieBar();
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

        // Ajax-Request also could render HTML-Output. So it could be that it is detected as PageTemplate.
        // So normally Ajax-Request does not have pageModel as attribute. Therefore, check the parameter
        if (
            !$this->cookiebarModel instanceof CookiebarModel ||
            !$this->objPage instanceof PageModel ||
            !$this->scopeMatcher->isFrontendRequest($request) ||
            !$request->attributes->has('pageModel')
        )
        {
            return;
        }

        $response = $event->getResponse();
        $content = $response->getContent();

        if ($this->isPageTemplate($event) === true)
        {
            // @TODO maybe we can check $this->objRootPage->enableCsp but if other ResponseListener set CSP we also want to process
            $nonce = $this->getScriptNonce($response);
            $cookieBarScript = $this->generateCookieBarScript($nonce);

            if ($this->cookiebarModel->position === 'bodyAboveContent')
            {
                preg_match('/<body([^>]*)>/', $content, $matches, PREG_OFFSET_CAPTURE);
                if (is_array($matches) && count($matches) > 0)
                {
                    $posValue = ($matches[0][0] ?? null);
                    $pos = ($matches[0][1] ?? null);
                    if (is_int($pos) && is_string($posValue))
                    {
                        $pos += strlen($posValue);
                        $content = substr($content, 0, $pos) . "\n" . $cookieBarScript . "\n" . substr($content, $pos);
                    }
                }
            } else
            {
                $pos = strripos($content, '</body>');
                if (false !== $pos)
                {
                    $content = substr($content, 0, $pos) . "\n" . $cookieBarScript . "\n" . substr($content, $pos);
                }
            }

            $content = $this->injectGlobalJs($content, $nonce);
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
     * @param Model $model
     * @param string $buffer
     * @param mixed $context
     * @return string
     * This is for legacyTemplates without Controller. @TODO to be removed in future
     */
    public function onParseLegacyTemplates(Model $model, string $buffer, mixed $context): string
    {
        // if !$module instanceof ModuleProxy || ContentProxy then it´s currently not a Fragment
        if (
            $context instanceof ModuleProxy ||
            $context instanceof ContentProxy ||
            !$this->cookiebarModel instanceof CookiebarModel ||
            !$this->objPage instanceof PageModel
        )
        {
            return $buffer;
        }

        return $this->parseTemplates($model, $buffer);
    }

    /**
     * @return void
     */
    private function prepareCookieBar(): void
    {
        if (
            !$this->objRootPage instanceof PageModel ||
            !$this->objPage instanceof PageModel
        )
        {
            return;
        }

        $this->cookiebarModel = Cookiebar::getConfigByPage($this->objRootPage);

        if (!$this->cookiebarModel instanceof CookiebarModel)
        {
            $this->cookiebarModel = null;
            return;
        }

        $this->types = Cookiebar::getIframeTypes();
        $this->cookies = Cookiebar::validateCookies($this->cookiebarModel);

        $this->rootPageBuffer['template'] = Cookiebar::parseCookiebarTemplate($this->cookiebarModel, $this->objRootPage->language);

        // Always add cache busting
        $javascript = 'bundles/contaocookiebar/scripts/cookiebar.min.js';
        $mtime = (string)filemtime($this->getRealPath($javascript));

        $this->rootPageBuffer['scriptCookieBar'] = null;
        if ($this->cookiebarModel->scriptPosition === 'body')
        {
            $this->rootPageBuffer['scriptCookieBar'] = $javascript . '?v=' . substr(md5($mtime), 0, 8);
        } else
        {
            $this->globalJavaScript = $javascript . '?v=' . substr(md5($mtime), 0, 8);
        }

        $this->rootPageBuffer['scriptConfigPattern'] = "var cookiebar = new ContaoCookiebar({configId:%s,pageId:%s,version:%s,lifetime:%s,consentLog:%s,token:'%s',doNotTrack:%s,currentPageId:%s,excludedPageIds:%s,cookies:%s,configs:%s,disableTracking:%s,texts:{acceptAndDisplay:'%s'}});";
        $this->rootPageBuffer['scriptConfigValues'] = [
            $this->cookiebarModel->id,
            $this->cookiebarModel->pageId,
            $this->cookiebarModel->version,
            $this->lifetime,
            $this->consentLog ? 1 : 0,
            $this->storageKey,
            $this->considerDnt ? 1 : 0,
            $this->objPage->id,
            json_encode(StringUtil::deserialize($this->cookiebarModel->excludePages)),
            json_encode(Cookiebar::validateCookies($this->cookiebarModel)),
            json_encode(Cookiebar::validateGlobalConfigs($this->cookiebarModel)),
            $this->tokenChecker->hasBackendUser() && !!$this->cookiebarModel->disableTrackingWhileLoggedIn ? 1 : 0,
            $this->translator->trans('tl_cookiebar.acceptAndDisplayLabel', [], 'contao_default')
        ];

    }

    /**
     * @param Model $model
     * @param string $buffer
     * @return string
     * s
     */
    private function parseTemplates(Model $model, string $buffer): string
    {
        if (
            !$this->cookiebarModel instanceof CookiebarModel ||
            !$this->objPage instanceof PageModel ||
            !is_array($this->types)
        )
        {
            return $buffer;
        }

        $template = $model->typePrefix . $model->type;
        if ($model->customTpl && $model->customTpl !== '')
        {
            $template = $model->customTpl;
        }

        foreach ($this->types as $strType => $arrTemplates)
        {
            if (!in_array($template, $arrTemplates))
            {
                continue;
            }

            foreach ($this->cookies as $cookie)
            {
                if (!isset($cookie['iframeType']) || $cookie['iframeType'] !== $strType)
                {
                    continue;
                }

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
                        $iframePattern = $matches[0][$i];
                        $quote = $matches[2][$i];

                        // @TODO check if this logic is fine
                        // check if nested content ist still parsed
                        // e.g. when using youtubeElement as insertTag in htmlElement and htmlElement is also defined as iframeType
                        // 16 = youtubeElement
                        // <div id="youtubeInsert">{{insert_content::16}}</div>
                        if (
                            !str_contains($iframePattern, 'data-ccb-id=' . $quote) &&
                            !str_contains($iframePattern, 'src=' . $quote . $strBlockUrl)
                        )
                        {
                            $search = 'src=' . $quote;
                            $replace = 'data-ccb-id=' . $quote . $cookie['id'] . $quote . '  src=' . $quote . $strBlockUrl . urlencode($matches[3][$i]) . $quote . ' data-src=' . $quote;

                            $iframe = str_replace($search, $replace, $iframePattern);
                            $buffer = str_replace($iframePattern, $iframe, $buffer);
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
            !$this->scopeMatcher->isFrontendMainRequest($event) ||
            $request->isXmlHttpRequest() ||
            (!$response->isSuccessful() && !$response->isClientError())
        )
        {
            return false;
        }

        if (
            'html' !== $request->getRequestFormat() ||
            !str_contains((string)$response->headers->get('Content-Type'), 'text/html') ||
            false !== stripos((string)$response->headers->get('Content-Disposition'), 'attachment;')
        )
        {
            return false;
        }

        if (false === strripos($response->getContent(), '</body>'))
        {
            return false;
        }

        return true;
    }

    /**
     * @param string $content
     * @param mixed $nonce
     * @return string
     * see also Contao\CoreBundle\EventListener\PreviewToolbarListener
     */
    private function injectGlobalJs(string $content, mixed $nonce): string
    {
        if (!!$this->globalJavaScript)
        {
            $pos = strripos($content, '</head>');

            if (false !== $pos)
            {
                $script = '<script' . ($nonce ? ' nonce="' . $nonce . '"' : '') . ' src="' . $this->globalJavaScript . '"></script>';
                $content = substr($content, 0, $pos) . "\n" . $script . "\n" . substr($content, $pos);
            }
        }

        return $content;
    }

    /**
     * @param mixed $nonce
     * @return string
     */
    private function generateCookieBarScript(mixed $nonce): string
    {
        $strHtml = $this->rootPageBuffer['template'];

        if ($this->rootPageBuffer['scriptCookieBar'] !== null)
        {
            $strHtml .= '<script' . ($nonce ? ' nonce="' . $nonce . '"' : '') . ' src="' . $this->rootPageBuffer['scriptCookieBar'] . '"></script>';
        }


        if (is_string($nonce))
        {
            $arrConfigValues = [' nonce="' . $nonce . '"', ...$this->rootPageBuffer['scriptConfigValues']];
            $strHtml .= vsprintf('<script%s>' . $this->rootPageBuffer['scriptConfigPattern'] . '</script>', $arrConfigValues);

        } else
        {
            $strHtml .= vsprintf('<script>' . $this->rootPageBuffer['scriptConfigPattern'] . '</script>', $this->rootPageBuffer['scriptConfigValues']);
        }

        return $strHtml;

    }

    /**
     * @param string $strFile
     * @return string
     */
    private function getRealPath(string $strFile): string
    {
        $container = System::getContainer();
        $strRootDir = $container->getParameter('kernel.project_dir');

        // Check the source file
        if (!file_exists($strRootDir . '/' . $strFile))
        {
            $strWebDir = StringUtil::stripRootDir($container->getParameter('contao.web_dir'));
            $webDirPath = $strRootDir . '/' . $strWebDir . '/' . $strFile;

            if (file_exists($webDirPath))
            {
                return $webDirPath;
            }
        }

        return $strFile;
    }

    /**
     * @param Response $response
     * @return string|null
     */
    private function getScriptNonce(Response $response): ?string
    {
        $cspHeader = $response->headers->get('Content-Security-Policy');
        if ($cspHeader === null || $cspHeader === '')
        {
            return null;
        }

        $directives = $this->cspParser->parseHeader($cspHeader);
        $scriptDirective = $directives->getDirective('script-src');
        if (is_string($scriptDirective) && str_contains($scriptDirective, 'nonce-'))
        {
            $noncePattern = explode(' ', $scriptDirective);
            foreach ($noncePattern as $value)
            {
                if (str_contains($value, 'nonce-'))
                {
                    return substr(str_replace("'nonce-", "", $value), 0, -1);
                }
            }

        }

        return null;

    }

}
