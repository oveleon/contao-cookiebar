<?php

namespace Oveleon\ContaoCookiebar\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class ContaoCookiebarTypeMigration extends AbstractMigration
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
        return 'Contao Cookiebar: Change types for Cookies from youtube and vimeo';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        // If the database table itself does not exist we should do nothing
        if (!$schemaManager->tablesExist(['tl_cookie'])) {
            return false;
        }

        $columns  = $schemaManager->listTableColumns('tl_cookie');

        $oldTypes = $this->connection->query("SELECT id FROM tl_cookie WHERE type IN('youtube', 'vimeo')");

        return $oldTypes->rowCount() > 0 && (isset($columns['iframetype']) || isset($columns['iframeType']));
    }

    public function run(): MigrationResult
    {
        // Overwrite the cookie types Youtube and Vimeo to iFrame and set the iFrame type dynamically
        $this->connection->prepare("UPDATE tl_cookie SET iframeType = type, type = 'iframe' WHERE type = 'youtube' OR type = 'vimeo'")->execute();

        return new MigrationResult(
            true,
            'Cookie types YouTube and Vimeo were successfully mapped as type iFrame.'
        );
    }
}
