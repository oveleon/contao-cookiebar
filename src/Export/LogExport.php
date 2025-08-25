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

namespace Oveleon\ContaoCookiebar\Export;

use Contao\Config;
use Contao\Database;
use Contao\File;
use Contao\StringUtil;
use Contao\System;

class LogExport
{
    /**
     * Export log
     *
     * @throws \Exception
     */
    public function export(): void
    {
        $offset = 0;
        $batch = 50000;

        $dateFormat = $this->convertToSQLDateFormat(Config::get('dateFormat')); // d.m.Y
        $timeFormat = $this->convertToSQLDateFormat(Config::get('timeFormat')); // H:i

        $objFile = new File('system/tmp/' . md5(uniqid((string) mt_rand(), true)));
        $objFile->write('');
        $objFile->close();

        $realPath = System::getContainer()->getParameter('kernel.project_dir') . '/' . $objFile->path;

        $handle = fopen($realPath, 'w');

        if (false === $handle)
        {
            // Maybe fall back to memory extensive array in this case?
            $objFile->write('Could not download the consent log due to missing write permissions within system/tmp');
            $objFile->close();
            $objFile->sendToBrowser('consent-log-error.txt');
        }

        $header = ['id', 'config', 'version', 'date', 'time', 'domain', 'url', 'ip', 'confirmed'];

        fputcsv($handle, $header, ';');

        $db = Database::getInstance();

        do {
            $result = $db->execute("SELECT
                    `id`, `cid` as config, `version`,
                    FROM_UNIXTIME(tstamp, '".$dateFormat."') as date,
                    FROM_UNIXTIME(tstamp, '".$timeFormat."') as time,
                    domain, url, ip,
                    config as confirmed
                FROM tl_cookie_log
                LIMIT $offset, $batch"
            );

            $data = $result->fetchAllAssoc();

            if (!empty($data))
            {
                foreach ($data as $row)
                {
                    if (!empty($row['confirmed']))
                    {
                        $arrServices = StringUtil::deserialize($row['confirmed']);
                        $arrConfig = [];

                        if (null !== $arrServices)
                        {
                            foreach ($arrServices as $i => $item)
                            {
                                $arrConfig[] = ($i + 1) . '. ' . $item['title'] . ' (ID: ' . $item['id'] . ', Cookie-Names: ' . ($item['token'] ?: '-') . ')';
                            }
                        }

                        $row['confirmed'] = implode("\n", $arrConfig);
                    }

                    fputcsv($handle, $row, ';');
                }
            }

            $offset += $batch;
        }
        while (
            !empty($data)
        );

        fclose($handle);

        $objFile->sendToBrowser('consent-log.csv');
    }

    /**
     * Convert the PHP time format syntax to mysql and mariadb syntax
     * https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters
     * https://mariadb.com/kb/en/from_unixtime/
     */
    private function convertToSQLDateFormat(string $format): string
    {
        $formatMapping = [
            'd'=>'%d','D'=>'%a','j'=>'%e','l'=>'%W','N'=>'%u','S'=>'','w'=>'%w','z'=>'%j', // Day
            'W'=>'%V', // Week
            'F'=>'%M','m'=>'%m','M'=>'%b','n'=>'%c', // Month
            'Y'=>'%Y','y'=>'%y', // Year
            'a'=>'%p','A'=>'%p','g'=>'%l','G'=>'%k','h'=>'%h','H'=>'%H','i'=>'%i','s'=>'%s','u'=>'%f', // Time
            'e'=>'','I'=>'','O'=>'','P'=>'','T'=>'','Z'=>'', // Timezone
        ];

        return strtr($format, $formatMapping);
    }
}
