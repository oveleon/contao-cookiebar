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

        $fieldConversion = 32 === $columns['gcmmode']->getLength();
        $valueConversion = $this->connection->fetchOne("SELECT TRUE FROM tl_cookie WHERE type = 'googleConsentMode' AND NOT gcmMode = '' AND gcmMode NOT LIKE 'a:%'");

        return $fieldConversion || $valueConversion;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeQuery("ALTER TABLE tl_cookie CHANGE gcmMode gcmMode VARCHAR(255) DEFAULT '' NOT NULL");

        $values = $this->connection->fetchAllKeyValue("SELECT id, gcmMode FROM tl_cookie WHERE type = 'googleConsentMode' AND NOT gcmMode = '' AND gcmMode NOT LIKE 'a:%'");

        foreach ($values as $id => $value)
        {
            $this->connection->update('tl_cookie', ['gcmMode' => serialize([$value])], ['id' => (int) $id]);
        }

        return $this->createResult(true, 'Old consent mode option have been serialized.');
    }
}
