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

namespace Oveleon\ContaoCookiebar\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class CookiebarTwigTemplatesMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    #[\Override]
    public function getName(): string
    {
        return 'Contao Cookiebar: Cookiebar twig templates migration';
    }

    /**
     * @throws Exception
     */
    public function shouldRun(): bool
    {
        $schemaManager =  $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_cookiebar', 'tl_cookie']))
        {
            return false;
        }

        $total = $this->connection->fetchOne('SELECT COUNT(*) FROM tl_cookiebar WHERE template=? OR template=? OR template=?', ['cookiebar_default', 'cookiebar_default_deny', 'cookiebar_simple']);
        $total += $this->connection->fetchOne('SELECT COUNT(*) FROM tl_cookie WHERE blockTemplate=?', ['ccb_element_blocker']);

        return $total > 0;
    }

    /**
     * @throws Exception
     */
    public function run(): MigrationResult
    {
        $cookiebarTemplates = $this->connection->fetchAllAssociative('SELECT id, template FROM tl_cookiebar WHERE template=? OR template=? OR template=?', ['cookiebar_default', 'cookiebar_default_deny', 'cookiebar_simple']);
        $cookieTemplates = $this->connection->fetchAllAssociative('SELECT id, blockTemplate FROM tl_cookie WHERE blockTemplate=?', ['ccb_element_blocker']);

        foreach ($cookiebarTemplates as $cookiebar)
        {
            $replacement = match ($cookiebar['template'] ?? null) {
                'cookiebar_default_deny' => 'cookiebar/default/deny',
                'cookiebar_default' => 'cookiebar/default',
                'cookiebar_simple' => 'cookiebar/simple/deny',
                default => null,
            };

            if ($replacement !== null)
            {
                $this->connection->update('tl_cookiebar', ['template' => $replacement], ['id' => $cookiebar['id']]);
            }
        }

        foreach ($cookieTemplates as $cookie)
        {
            if ('ccb_element_blocker' === ($cookie['blockTemplate'] ?? null))

            $this->connection->update('tl_cookie', ['blockTemplate' => 'ccb/element_blocker'], ['id' => $cookie['id']]);
        }

        return $this->createResult(true);
    }
}
