<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @author      Sebastian Zoglowek    <https://github.com/zoglo>
 * @copyright   Oveleon               <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\Controller;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Oveleon\ContaoCookiebar\Cookiebar;
use Oveleon\ContaoCookiebar\Model\CookieModel;
use Oveleon\ContaoCookiebar\Utils\TwigRenderTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/cookiebar', defaults: ['_scope' => 'frontend'])]
readonly class CookiebarController
{
    use TwigRenderTrait;

    public function __construct(
        private ContaoFramework $framework,
        private TranslatorInterface $translator,
    ){}

    /**
     * Block content
     */
    #[Route('/block/{locale}/{id}', name: 'cookiebar_block')]
    public function block(Request $request, string $locale, int $id): Response
    {
        System::loadLanguageFile('tl_cookiebar', $locale);

        $this->framework->initialize();

        $objCookie = CookieModel::findById($id);

        if (null === $objCookie || null === $request->headers->get('referer'))
        {
            throw new PageNotFoundException();
        }

        if (!Validator::isLocale($locale))
        {
            return new Response('The URL must contain a valid locale.', Response::HTTP_BAD_REQUEST);
        }

        // Protect against XSS attacks
        $strUrl = StringUtil::stripInsertTags(StringUtil::specialchars($request->get('redirect')));

        if (!Validator::isUrl($strUrl))
        {
            return new Response('The redirect destination must be a valid URL.', Response::HTTP_BAD_REQUEST);
        }

        return new Response($this->renderTwigTemplate($objCookie->blockTemplate ?: 'ccb/element_blocker', [
            'locale' => $locale,
            'cookie' => $objCookie,
            'redirect' => $strUrl,
        ]));
    }

    /**
     * Execute various functions
     */
    #[Route('/{module}', name: 'cookiebar_prepare', defaults: ['_token_check' => false])]
    public function execute(Request $request, $module): JsonResponse
    {
        $this->framework->initialize();

        switch($module)
        {
            // Delete cookies by their tokens
            case 'delete':
                // The `delete` route is now called via POST, so the query string must be split.
                $queryString = urldecode($request->getContent());
                parse_str($queryString, $request);

                if ($error = $this->errorMissingParameter($request, ['tokens']))
                {
                      return $error;
                }

                Cookiebar::deleteCookieByToken($request['tokens']);
                break;

            // Add new log entry
            case 'log':
                if ($error = $this->errorMissingParameter($request, ['configId']))
                {
                      return $error;
                }

                Cookiebar::log((int) $request->get('configId'), $request->get('referrer'), null, $request->get('cookies'));
                break;

            //
            default:
                throw new NotFoundHttpException();
        }

        return new JsonResponse(['type' => $module, 'status' => 'OK']);
    }

    /**
     * Return error
     */
    private function error(string $msg): JsonResponse
    {
        return new JsonResponse(['error' => 1, 'message' => $msg]);
    }

    /**
     * Return error if the given parameters are not set
     */
    private function errorMissingParameter($request, array $arrParameter): JsonResponse|null
    {
        foreach ($arrParameter as $parameter)
        {
            if (is_array($request))
            {
                if (!isset($request[$parameter]))
                {
                    return $this->error('Missing parameter: ' . $parameter);
                }
            }
            elseif (!$request->get($parameter))
            {
                return $this->error('Missing parameter: ' . $parameter);
            }
        }

        return null;
    }
}
