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

class CookiebarController extends AbstractController
{
    /**
     * Runs the command scheduler. (prepare)
     *
     * @param Request $request
     * @param $module
     * @param $id
     *
     * @return JsonResponse|string
     */
    public function prepareAction(Request $request, $module, $id)
    {
        $this->container->get('contao.framework')->initialize();
        $arrResponse = [];

        switch($module)
        {
            // Save a full set of cookies
            case 'save':
                if($error = $this->errorMissingParameters($request, ['configId','pageId']))
                {
                    return $error;
                }

                $objConfig = Cookiebar::getConfigByPage($request->get('pageId'));
                $arrResponse = Cookiebar::validateCookies($objConfig, $request->get('cookies') ?: []);

                Cookiebar::setCookie(json_encode([
                    'configId' => $request->get('configId'),
                    'pageId'   => $request->get('pageId'),
                    'version'  => $request->get('version') ?: $objConfig->version,
                    'cookies'  => $request->get('cookies')
                ]));

                Cookiebar::log($objConfig, null, $request->get('referrer'));
                break;

            // Push cookie id to current set of cookies
            case 'push':
                if($error = $this->errorMissingParameters($request, ['configId']))
                {
                    return $error;
                }

                if(!$id)
                {
                    $this->error('This route can only be called up with an ID');
                }

                $objCookie = Cookiebar::getCookie();
                $objCookie['cookies'][] = $id;

                Cookiebar::setCookie(json_encode($objCookie));
                Cookiebar::log(Cookiebar::getConfig($request->get('configId')), null, $request->get('referrer'));
                break;

            // Check whether a cookie was accepted based on the ID or the token
            case 'isset':
                if($error = $this->errorMissingParameters($request, ['pageId']))
                {
                    return $error;
                }

                if(!$id)
                {
                    $this->error('This route can only be called up with an ID');
                }

                $arrResponse[ $id ] = Cookiebar::issetCookie($id, $request->get('pageId'));
                break;
        }

        return new JsonResponse($arrResponse);
    }

    /**
     * Runs the command scheduler. (block)
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function blockAction(Request $request, $id)
    {
        System::loadLanguageFile('tl_cookiebar');

        $objCookie = CookieModel::findById($id);

        if(null === $objCookie)
        {
            return new Response('');
        }

        /** @var FrontendTemplate $objTemplate */
        $objTemplate = new FrontendTemplate('ccb_element_blocker');

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
     * @param array $arrParameters
     *
     * @return JsonResponse
     */
    private function errorMissingParameters(Request $request, array $arrParameters)
    {
        foreach ($arrParameters as $parameter)
        {
            if(!$request->get($parameter))
            {
                return $this->error('Missing parameter: ' . $parameter);
            }
        }

        return null;
    }
}
