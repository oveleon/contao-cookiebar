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

class ConsentModeMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    #[\Override]
    public function getName(): string
    {
        return 'Contao Cookiebar: Consent Mode Migration';
    }

    /**
     * @throws Exception
     */
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

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
