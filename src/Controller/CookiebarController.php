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

use Contao\FrontendTemplate;
use Contao\System;
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
     * @Route("/cookiebar/block/{id}", name="cookiebar_block")
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function blockAction(Request $request, $id)
    {
        $this->framework->initialize();

        System::loadLanguageFile('tl_cookiebar');

        $objCookie = CookieModel::findById($id);

        if(null === $objCookie)
        {
            return new Response('');
        }

        /** @var FrontendTemplate $objTemplate */
        $objTemplate = new FrontendTemplate($objCookie->blockTemplate ?: 'ccb_element_blocker');

        $objTemplate->language = $GLOBALS['TL_LANGUAGE'];
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
                if($error = $this->errorMissingParameter($request, ['tokens']))
                {
                    return $error;
                }

                Cookiebar::deleteCookieByToken($request->get('tokens'));
                break;

            // Add new log entry
            case 'log':
                if($error = $this->errorMissingParameter($request, ['configId','version']))
                {
                    return $error;
                }

                Cookiebar::log($request->get('configId'), $request->get('version'), null, $request->get('referrer'),null, $request->get('cookies'));
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
     * @param Request $request
     * @param array $arrParameter
     *
     * @return JsonResponse
     */
    private function errorMissingParameter(Request $request, array $arrParameter)
    {
        foreach ($arrParameter as $parameter)
        {
            if(!$request->get($parameter))
            {
                return $this->error('Missing parameter: ' . $parameter);
            }
        }

        return null;
    }
}
