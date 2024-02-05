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
 * Reads and writes cookiebar group configurations
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $tstamp
 * @property string  $identifier
 * @property integer $sorting
 * @property string  $title
 * @property string  $description
 * @property boolean $published
 *
 * @method static CookieGroupModel|null findById($id, array $opt=array())
 * @method static CookieGroupModel|null findByPk($id, array $opt=array())
 * @method static CookieGroupModel|null findOneBy($col, $val, array $opt=array())
 * @method static CookieGroupModel|null findOneByPid($val, array $opt=array())
 * @method static CookieGroupModel|null findOneByPtable($val, array $opt=array())
 * @method static CookieGroupModel|null findOneByTstamp($val, array $opt=array())
 * @method static CookieGroupModel|null findOneBySorting($val, array $opt=array())
 * @method static CookieGroupModel|null findOneByTitle($val, array $opt=array())
 * @method static CookieGroupModel|null findOneByPublished($val, array $opt=array())
 *
 * @method static Collection|CookieGroupModel[]|CookieGroupModel|null findByPid($val, array $opt=array())
 * @method static Collection|CookieGroupModel[]|CookieGroupModel|null findByPtable($val, array $opt=array())
 * @method static Collection|CookieGroupModel[]|CookieGroupModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|CookieGroupModel[]|CookieGroupModel|null findBySorting($val, array $opt=array())
 * @method static Collection|CookieGroupModel[]|CookieGroupModel|null findByTitle($val, array $opt=array())
 * @method static Collection|CookieGroupModel[]|CookieGroupModel|null findByPublished($val, array $opt=array())
 * @method static Collection|CookieGroupModel[]|CookieGroupModel|null findMultipleByIds($var, array $opt=array())
 * @method static Collection|CookieGroupModel[]|CookieGroupModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|CookieGroupModel[]|CookieGroupModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTitle($val, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countByPtable($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countBySorting($val, array $opt=array())
 * @method static integer countByPublished($val, array $opt=array())
 */
class CookieGroupModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_cookie_group';

    /**
     * Find published cookie groups by their PID
     */
    public static function findPublishedByPid(int $intPid, array $arrOptions=array()): Collection|array|CookieGroupModel|null
    {
        $t = static::$strTable;

        $arrColumns = array(
            "$t.pid=?",
            "$t.published='1'"
        );

        $arrOptions['order'] = "$t.sorting";

        return static::findBy($arrColumns, $intPid, $arrOptions);
    }
}
