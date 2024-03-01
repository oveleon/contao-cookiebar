<?php

namespace Oveleon\ContaoCookiebar\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ResponseContext\Csp\CspHandler;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\PageRegular;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoCookiebar\Cookiebar;
use Oveleon\ContaoCookiebar\Model\CookiebarModel;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsHook('generatePage')]
class GeneratePageListener
{
    private PageModel $objRootPage;

    private PageModel $objPage;

    private LayoutModel $layout;

    private PageRegular $pageRegular;

    private string $buffer = '';
    private ?CookiebarModel $cookiebarModel = null;

    public function __construct(
        private readonly TranslatorInterface     $translator,
        private readonly TokenChecker            $tokenChecker,
        private readonly ResponseContextAccessor $responseContextAccessor,
        private readonly int                     $lifetime,
        private readonly bool                    $consentLog,
        private readonly string                  $storageKey,
        private readonly bool                    $considerDnt
    )
    {
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        $this->objPage = $pageModel;
        $this->objRootPage = 'root' === $pageModel->type ? $pageModel->type : PageModel::findByPk($pageModel->rootId);
        $this->layout = $layout;
        $this->pageRegular = $pageRegular;

        $this->prepareCookiebar();
    }

    private function prepareCookiebar(): void
    {
        $this->cookiebarModel = Cookiebar::getConfigByPage($this->objRootPage);

        if (!$this->cookiebarModel instanceof CookiebarModel)
        {
            $this->cookiebarModel = null;
            return;
        }

        $strHtml = Cookiebar::parseCookiebarTemplate($this->cookiebarModel, $this->objRootPage->language);

        // Content security policy
        $nonce = null;

        // If bodyBelowContent has been selected, nonce gets added automatically
        if ('bodyBelowContent' !== $this->cookiebarModel->position)
        {
            $responseContext = $this->responseContextAccessor->getResponseContext();

            if ($responseContext?->has(CspHandler::class))
            {
                /** @var CspHandler $csp */
                $cspHandler = $responseContext->get(CspHandler::class);
                $nonce = $cspHandler->getNonce('script-src');
            }
        }

        // Always add cache busting
        $javascript = 'bundles/contaocookiebar/scripts/cookiebar.min.js';
        $mtime = (string)filemtime($this->getRealPath($javascript));
        $javascript .= '?v=' . substr(md5($mtime), 0, 8);

        match ($this->cookiebarModel->scriptPosition)
        {
            'body' => $strHtml .= '<script' . ($nonce ? ' nonce="' . $nonce . '"' : '') . ' src="' . $javascript . '"></script>',
            default => $GLOBALS['TL_JAVASCRIPT'][] = $javascript
        };

        $strHtml .= vsprintf("<script%s>var cookiebar = new ContaoCookiebar({configId:%s,pageId:%s,version:%s,lifetime:%s,consentLog:%s,token:'%s',doNotTrack:%s,currentPageId:%s,excludedPageIds:%s,cookies:%s,configs:%s,disableTracking:%s, texts:{acceptAndDisplay:'%s'}});</script>", [
            $nonce ? ' nonce="' . $nonce . '"' : '',
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
        ]);

        $this->buffer = $strHtml;

        match ($this->cookiebarModel->position)
        {
            'bodyBelowContent' => $this->layout->script .= $this->buffer,
            default => $this->setBodyAboveContent()
        };
    }

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

    private function setBodyAboveContent(): void
    {
        $positions = $this->pageRegular->Template->positions;
        $sections = $this->pageRegular->Template->sections;

        $positions['top']['contao-cookiebar'] = [
            'template' => 'contaocookiebar_top',
            'id' => 'contao-cookiebar',
            'position' => 'top'
        ];

        $sections['contao-cookiebar'] = $this->buffer;

        $this->pageRegular->Template->positions = $positions;
        $this->pageRegular->Template->sections = $sections;

        $this->pageRegular->Template->parse();
    }
}
