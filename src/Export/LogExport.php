<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\Export;

use Contao\Config;
use Contao\File;
use Contao\StringUtil;
use Oveleon\ContaoCookiebar\Model\CookieLogModel;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class LogExport
{
    /**
     * Export log
     */
    public function export(): void
    {
        $objLog = CookieLogModel::findAll();
        $arrLog = [];

        if(null !== $objLog)
        {
            while($objLog->next())
            {
                $arrRow = [];

                $arrRow['id'] = $objLog->id;
                $arrRow['config'] = $objLog->cid;
                $arrRow['version'] = $objLog->version;
                $arrRow['date'] = date(Config::get('dateFormat'), $objLog->tstamp);
                $arrRow['time'] = date(Config::get('timeFormat'), $objLog->tstamp);
                $arrRow['domain'] = $objLog->domain;
                $arrRow['url'] = $objLog->url;
                $arrRow['ip'] = $objLog->ip;

                $arrServices = StringUtil::deserialize($objLog->config);
                $arrConfig = [];

                if(null !== $arrServices)
                {
                    $x = 1;
                    foreach($arrServices as $item)
                    {
                        $arrConfig[] = $x . '. ' . $item['title'] . ' (ID: ' . $item['id'] . ', Cookie-Names: ' . ($item['token'] ?: '-') . ')';
                        $x++;
                    }
                }

                $arrRow['confirmed'] =  implode("\n", $arrConfig);
                $arrLog[] = $arrRow;
            }
        }

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $strData = $serializer->encode($arrLog, 'csv', [
            'csv_delimiter' => ';'
        ]);

        $objFile = new File('system/tmp/' . md5(uniqid(mt_rand(), true)));
        $objFile->write($strData);
        $objFile->close();

        $objFile->sendToBrowser('consent-log.csv');
    }
}
