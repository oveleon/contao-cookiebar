<?php

namespace Oveleon\ContaoCookiebar\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class ContaoCookiebarCookieMigration extends AbstractMigration
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

        // If the database table itself does not exist we should do nothing
        if (!$schemaManager->tablesExist(['tl_cookie'])) {
            return false;
        }

        $oldCookies = $this->connection->query("SELECT id FROM tl_cookie WHERE token = 'ccb_contao_token'");

        return $oldCookies->rowCount() > 0;
    }

    public function run(): MigrationResult
    {
        // Delete old cookies
        $this->connection->prepare("DELETE FROM tl_cookie WHERE token = 'ccb_contao_token'")->execute();

        return new MigrationResult(
            true,
            'Contao Cookiebar cookie token was successfully removed from the configurations.'
        );
    }
}
