<?php

namespace Oveleon\ContaoCookiebar\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

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

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist(['tl_cookie'])) {
            return false;
        }

        return $this->connection->fetchOne("SELECT TRUE FROM tl_cookie WHERE type = 'googleConsentMode' AND NOT gcmMode = '' AND gcmMode NOT LIKE 'a:%'");
    }

    public function run(): MigrationResult
    {
        $values = $this->connection->fetchAllKeyValue("SELECT id, gcmMode FROM tl_cookie WHERE type = 'googleConsentMode' AND NOT gcmMode = '' AND gcmMode NOT LIKE 'a:%'");

        foreach ($values as $id => $value)
        {
            $this->connection->update('tl_cookie', ['gcmMode' => serialize([$value])], ['id' => (int) $id]);
        }


        return $this->createResult(true, 'Old consent mode option have been serialized.');
    }
}
