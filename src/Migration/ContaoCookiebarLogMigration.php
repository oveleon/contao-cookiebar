<?php

namespace Oveleon\ContaoCookiebar\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class ContaoCookiebarLogMigration extends AbstractMigration
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_cookie_log');

        return !isset($columns['cid']);
    }

    public function run(): MigrationResult
    {
        // The field and index pid must be renamed to cid
        $this->connection->prepare("ALTER TABLE tl_cookie_log CHANGE pid cid INT(10) UNSIGNED NOT NULL DEFAULT '0'")->execute();
        $this->connection->prepare("ALTER TABLE tl_cookie_log DROP INDEX pid, ADD INDEX cid (cid) USING BTREE")->execute();

        return new MigrationResult(
            true,
            'Cookiebar Log: The field pid was successfully renamed.'
        );
    }
}
