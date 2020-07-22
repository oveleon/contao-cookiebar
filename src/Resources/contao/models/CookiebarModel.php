<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar;

use Contao\Model;

/**
 * Reads and writes cookiebar configurations
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $title
 * @property string  $description
 * @property string  $infoUrls
 * @property string  $template
 * @property string  $alignment
 * @property string  $blocking
 * @property integer $version
 * @property integer $position
 * @property boolean $published
 *
 * @method static CookiebarModel|null findById($id, array $opt=array())
 * @method static CookiebarModel|null findByPk($id, array $opt=array())
 * @method static CookiebarModel|null findOneBy($col, $val, array $opt=array())
 * @method static CookiebarModel|null findOneByTstamp($val, array $opt=array())
 * @method static CookiebarModel|null findOneByTitle($val, array $opt=array())
 * @method static CookiebarModel|null findOneByPublished($val, array $opt=array())
 *
 * @method static \Model\Collection|CookiebarModel[]|CookiebarModel|null findByTstamp($val, array $opt=array())
 * @method static \Model\Collection|CookiebarModel[]|CookiebarModel|null findByTitle($val, array $opt=array())
 * @method static \Model\Collection|CookiebarModel[]|CookiebarModel|null findByPublished($val, array $opt=array())
 * @method static \Model\Collection|CookiebarModel[]|CookiebarModel|null findMultipleByIds($var, array $opt=array())
 * @method static \Model\Collection|CookiebarModel[]|CookiebarModel|null findBy($col, $val, array $opt=array())
 * @method static \Model\Collection|CookiebarModel[]|CookiebarModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTitle($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByPublished($val, array $opt=array())
 */
class CookiebarModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_cookiebar';
}
