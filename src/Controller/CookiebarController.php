<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\Controller;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\System;
use Contao\Validator;
use Oveleon\ContaoCookiebar\Cookiebar;
use Oveleon\ContaoCookiebar\CookieModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ContentApiController provides all routes.
 *
 * @Route(defaults={"_scope" = "frontend"})
 */
class CookiebarController extends AbstractController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Runs the command scheduler. (block)
     *
     * @Route("/cookiebar/block/{locale}/{id}", name="cookiebar_block")
     *
     * @param Request $request
     * @param         $id
     *
     * @return Response
     * @throws \Exception
     */
    public function blockAction(Request $request, $locale, $id)
    {
        $this->framework->initialize();

        System::loadLanguageFile('tl_cookiebar', $locale);

        $objCookie = CookieModel::findById($id);

        if (null === $objCookie || null === $request->headers->get('referer'))
        {
            throw new PageNotFoundException();
        }

        if(!Validator::isLocale($locale))
        {
            return new Response('The URL must contain a valid locale.', Response::HTTP_BAD_REQUEST);
        }

        // Protect against XSS attacks
        $strUrl = Input::get('redirect');

        if(!Validator::isUrl($strUrl))
        {
            return new Response('The redirect destination must be a valid URL.', Response::HTTP_BAD_REQUEST);
        }

        /** @var FrontendTemplate $objTemplate */
        $objTemplate = new FrontendTemplate($objCookie->blockTemplate ?: 'ccb_element_blocker');

        $objTemplate->language = $locale;
        $objTemplate->id = $objCookie->id;
        $objTemplate->title = $objCookie->title;
        $objTemplate->type = $objCookie->type;
        $objTemplate->iframeType = $objCookie->iframeType;
        $objTemplate->description = $objCookie->blockDescription;
        $objTemplate->redirect = $request->get('redirect');
        $objTemplate->acceptAndDisplayLabel = $GLOBALS['TL_LANG']['tl_cookiebar']['acceptAndDisplayLabel'];

        return $objTemplate->getResponse();
    }

    /**
     * Runs the command scheduler. (prepare)
     *
     * @Route("/cookiebar/{module}/{id}", name="cookiebar_prepare", defaults={"_token_check" = false, "id" = null})
     *
     * @param Request $request
     * @param $module
     * @param $id
     *
     * @return JsonResponse|string
     */
    public function prepareAction(Request $request, $module, $id)
    {
        $this->framework->initialize();

        switch($module)
        {
            // Delete cookies by their tokens
            case 'delete':
                // The `delete` route is now called via POST, so the query string must be split.
                $queryString = urldecode($request->getContent());
                parse_str($queryString, $request);

                if($error = $this->errorMissingParameter($request, ['tokens']))
                {
                    return $error;
                }

                Cookiebar::deleteCookieByToken($request['tokens']);
                break;

            // Add new log entry
            case 'log':
                if($error = $this->errorMissingParameter($request, ['configId']))
                {
                    return $error;
                }

                Cookiebar::log($request->get('configId'), $request->get('referrer'), null, $request->get('cookies'));
                break;
        }

        return new JsonResponse(['type' => $module, 'status' => 'OK']);
    }

    /**
     * Return error
     *
     * @param $msg
     *
     * @return JsonResponse
     */
    private function error($msg)
    {
        return new JsonResponse(['error' => 1, 'message' => $msg]);
    }

    /**
     * Return error if the given parameters are not set
     *
     * @param $request
     * @param array $arrParameter
     *
     * @return JsonResponse
     */
    private function errorMissingParameter($request, array $arrParameter)
    {
        foreach ($arrParameter as $parameter)
        {
            if(is_array($request))
            {
                if(!isset($request[$parameter]))
                {
                    return $this->error('Missing parameter: ' . $parameter);
                }
            }
            elseif(!$request->get($parameter))
            {
                return $this->error('Missing parameter: ' . $parameter);
            }
        }

        return null;
    }
}
