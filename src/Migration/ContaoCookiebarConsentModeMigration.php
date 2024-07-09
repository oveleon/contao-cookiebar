<?php

namespace Oveleon\ContaoCookiebar\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class ContaoCookiebarConsentModeMigration extends AbstractMigration
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
        return 'Contao Cookiebar: Consent Mode Migration';
    }

    /**
     * @throws Exception
     */
    public function shouldRun(): bool
    {
        $schemaManager = method_exists(Connection::class, 'createSchemaManager') ? $this->connection->createSchemaManager() : $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist(['tl_cookie']))
        {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_cookie');

        if (!isset($columns['gcmmode']))
        {
            return false;
        }

        // 1.16.1 || 2.1.1 migration
        $customConfiguration = $this->connection->fetchOne("SELECT TRUE FROM tl_cookie WHERE type = 'googleConsentMode' AND NOT gcmMode = '' AND NOT scriptConfig = ''");

        // 1.16.0 || 2.1.0 migration
        $fieldConversion = 32 === $columns['gcmmode']->getLength();
        $valueConversion = $this->connection->fetchOne("SELECT TRUE FROM tl_cookie WHERE type = 'googleConsentMode' AND NOT gcmMode = '' AND gcmMode NOT LIKE 'a:%'");

        return $customConfiguration || $fieldConversion || $valueConversion;
    }

    private function changeFieldSize(): void
    {
        $this->connection->executeQuery("ALTER TABLE tl_cookie CHANGE gcmMode gcmMode VARCHAR(255) DEFAULT '' NOT NULL");
    }

    /**
     * 1.16.0 -> 1.16.1 Migration for better usability. The script configuration is only shown when no consent mode has
     * been picked
     *
     * @return void
     */
    private function dropSelectedConsentModeWhenScriptConfigExists(): void
    {
        $values = $this->connection->fetchAllKeyValue("SELECT id, gcmMode FROM tl_cookie WHERE type = 'googleConsentMode' AND NOT gcmMode = '' AND NOT scriptConfig = ''");

        foreach ($values as $id => $value)
        {
            $this->connection->update('tl_cookie', ['gcmMode' => ''], ['id' => (int) $id]);
        }
    }

    /**
     * 1.16.0 Migration for changing the select field to a multi checkbox field
     *
     * @return void
     */
    private function convertGoogleConsentModeToMultiCheckboxField(): void
    {
        $values = $this->connection->fetchAllKeyValue("SELECT id, gcmMode FROM tl_cookie WHERE type = 'googleConsentMode' AND NOT gcmMode = '' AND gcmMode NOT LIKE 'a:%'");

        foreach ($values as $id => $value)
        {
            $this->connection->update('tl_cookie', ['gcmMode' => serialize([$value])], ['id' => (int) $id]);
        }
    }

    public function run(): MigrationResult
    {
        $this->changeFieldSize();
        $this->dropSelectedConsentModeWhenScriptConfigExists();
        $this->convertGoogleConsentModeToMultiCheckboxField();

        return $this->createResult(true, 'Consent mode configuration has been updated');
    }
}
