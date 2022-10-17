<?php

namespace Oveleon\ContaoCookiebar\EventListener\DataContainer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Contao\CoreBundle\ServiceAnnotation\Callback;

class CookieGroupCallbackListener
{
    use CookiebarTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Connection   $connection
    ){}

    /**
     * Handle multiple edit
     *
     * @Callback(table="tl_cookie_group", target="config.onload")
     *
     * @throws Exception
     */
    public function handleMultipleEdit(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = $this->requestStack->getSession();

        switch ($action = $request->get('act'))
        {
            case 'deleteAll':
            case 'copyAll':
            case 'cutAll':

                $sessionFields = $session->all();

                $currentIds = $action == 'deleteAll' ? $sessionFields['CURRENT']['IDS'] : $sessionFields['CLIPBOARD']['tl_cookie']['id'];

                // Set allowed cookie IDs (delete multiple)
                if (is_array($currentIds))
                {
                    $arrIds = [];

                    foreach ($currentIds as $id)
                    {
                        $objGroup = $this->connection->fetchOne("SELECT id, pid, identifier FROM tl_cookie_group WHERE id=?", [$id]);

                        if ($objGroup->numRows < 1)
                        {
                            continue;
                        }

                        // Locked groups cannot be deleted or copied
                        if ($objGroup->identifier !== 'lock')
                        {
                            $arrIds[] = $id;
                        }
                    }

                    if($action == 'deleteAll')
                    {
                        $sessionFields['CURRENT']['IDS'] = $arrIds;
                    }
                    else
                    {
                        if(empty($arrIds))
                        {
                            $sessionFields['CLIPBOARD']['tl_cookie'] = $arrIds;
                        }
                        else
                        {
                            $sessionFields['CLIPBOARD']['tl_cookie']['id'] = $arrIds;
                        }
                    }
                }

                // Overwrite session
                $session->replace($sessionFields);
        }
    }

    /**
     * Check if a button needs to be disabled
     *
     * @Callback(table="tl_cookie_group", target="list.operations.copy.button")
     * @Callback(table="tl_cookie_group", target="list.operations.cut.button")
     * @Callback(table="tl_cookie_group", target="list.operations.delete.button")
     */
    public function disableButton(array $row, ?string $href, string $label, string $title, ?string $icon, string $attributes): string
    {
        return $this->disableButtonOnLocked($row, $href, $label, $title, $icon, $attributes);
    }
}
