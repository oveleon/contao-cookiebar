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
 * Reads and writes cookiebar configurations
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $title
 * @property string  $description
 * @property string  $infoDescription
 * @property integer $version
 * @property boolean $updateVersion
 * @property string  $infoUrls
 * @property string  $excludePages
 * @property string  $buttonColorScheme
 * @property string  $template
 * @property string  $alignment
 * @property string  $blocking
 * @property integer $scriptPosition
 * @property integer $position
 * @property boolean $cssID
 *
 * @method static CookiebarModel|null findById($id, array $opt=array())
 * @method static CookiebarModel|null findByPk($id, array $opt=array())
 * @method static CookiebarModel|null findOneBy($col, $val, array $opt=array())
 * @method static CookiebarModel|null findOneByTstamp($val, array $opt=array())
 * @method static CookiebarModel|null findOneByTitle($val, array $opt=array())
 * @method static CookiebarModel|null findOneByDescription($val, array $opt=array())
 * @method static CookiebarModel|null findOneByVersion($val, array $opt=array())
 * @method static CookiebarModel|null findOneByUpdateVersion($val, array $opt=array())
 * @method static CookiebarModel|null findOneByInfoUrls($val, array $opt=array())
 * @method static CookiebarModel|null findOneByTemplate($val, array $opt=array())
 * @method static CookiebarModel|null findOneByAlignment($val, array $opt=array())
 * @method static CookiebarModel|null findOneByBlocking($val, array $opt=array())
 * @method static CookiebarModel|null findOneByPosition($val, array $opt=array())
 * @method static CookiebarModel|null findOneByCssID($val, array $opt=array())
 *
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByTitle($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByDescription($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByVersion($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByUpdateVersion($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByInfoUrls($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByTemplate($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByAlignment($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByBlocking($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByPosition($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByCssID($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findMultipleByIds($var, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByTitle($val, array $opt=array())
 * @method static integer countByDescription($val, array $opt=array())
 * @method static integer countByVersion($val, array $opt=array())
 * @method static integer countByUpdateVersion($val, array $opt=array())
 * @method static integer countByInfoUrls($val, array $opt=array())
 * @method static integer countByTemplate($val, array $opt=array())
 * @method static integer countByAlignment($val, array $opt=array())
 * @method static integer countByBlocking($val, array $opt=array())
 * @method static integer countByPosition($val, array $opt=array())
 * @method static integer countByCssID($val, array $opt=array())
 */
class CookiebarModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_cookiebar';
}
