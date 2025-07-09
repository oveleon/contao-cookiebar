<?php

declare(strict_types=1);

namespace Oveleon\ContaoCookiebar\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class ContaoCookiebarOpenerMigration extends AbstractMigration
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Contao Cookiebar: Cookiebar opener migration';
    }

    /**
     * @throws Exception
     */
    public function shouldRun(): bool
    {
        $schemaManager =  $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_content', 'tl_module']))
        {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_content');

        $total = $this->connection->fetchOne('SELECT COUNT(*) FROM tl_content WHERE type=?', ['cookiebarOpener']);
        $total += $this->connection->fetchOne('SELECT COUNT(*) FROM tl_module WHERE type=?', ['cookiebarOpener']);

        return $total > 0;
    }

    public function run(): MigrationResult
    {
        $elements = $this->connection->fetchAllAssociative('SELECT id FROM tl_content WHERE type=?', ['cookiebarOpener']);
        $modules = $this->connection->fetchAllAssociative('SELECT id FROM tl_module WHERE type=?', ['cookiebarOpener']);

        foreach ($elements as $element)
        {
            $this->connection->update('tl_content', ['type' => 'cookiebar_opener'], ['id' => $element['id']]);
        }

        foreach ($modules as $module)
        {
            $this->connection->update('tl_module', ['type' => 'cookiebar_opener'], ['id' => $module['id']]);
        }

        return $this->createResult(true);
    }
}
