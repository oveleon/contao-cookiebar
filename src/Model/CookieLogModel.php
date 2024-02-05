<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\Model;

use Contao\Model;
use Contao\Model\Collection;

/**
 * Reads and writes cookie logs
 *
 * @property integer $id
 * @property integer $cid
 * @property integer $version
 * @property integer $tstamp
 * @property string  $domain
 * @property string  $url
 * @property string  $ip
 * @property string  $config
 *
 * @method static CookieLogModel|null findById($id, array $opt=array())
 * @method static CookieLogModel|null findByPk($id, array $opt=array())
 * @method static CookieLogModel|null findOneBy($col, $val, array $opt=array())
 * @method static CookieLogModel|null findOneByTstamp($val, array $opt=array())
 * @method static CookieLogModel|null findOneByDomain($val, array $opt=array())
 * @method static CookieLogModel|null findOneByUrl($val, array $opt=array())
 * @method static CookieLogModel|null findOneByIp($val, array $opt=array())
 *
 * @method static Collection|CookieLogModel[]|CookieLogModel|null findByCid($val, array $opt=array())
 * @method static Collection|CookieLogModel[]|CookieLogModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|CookieLogModel[]|CookieLogModel|null findByDomain($val, array $opt=array())
 * @method static Collection|CookieLogModel[]|CookieLogModel|null findByUrl($val, array $opt=array())
 * @method static Collection|CookieLogModel[]|CookieLogModel|null findByIp($val, array $opt=array())
 * @method static Collection|CookieLogModel[]|CookieLogModel|null findMultipleByIds($var, array $opt=array())
 * @method static Collection|CookieLogModel[]|CookieLogModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|CookieLogModel[]|CookieLogModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByDomain($val, array $opt=array())
 * @method static integer countByUrl($val, array $opt=array())
 * @method static integer countByIp($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 */
class CookieLogModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_cookie_log';
}
