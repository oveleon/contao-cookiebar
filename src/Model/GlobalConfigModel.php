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
 * Reads and writes global configurations
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $title
 * @property string  $type
 * @property string  $vendorId
 * @property string  $sourceUrl
 * @property integer $sourceLoadingMode
 * @property string  $sourceUrlParameter
 * @property string  $scriptConfirmed
 * @property string  $scriptUnconfirmed
 * @property integer $scriptPosition
 * @property string  $scriptConfig
 * @property boolean $googleConsentMode
 * @property boolean $published
 *
 * @method static CookiebarModel|null findById($id, array $opt=array())
 * @method static CookiebarModel|null findByPk($id, array $opt=array())
 * @method static CookiebarModel|null findOneBy($col, $val, array $opt=array())
 * @method static CookiebarModel|null findOneByTstamp($val, array $opt=array())
 * @method static CookiebarModel|null findOneByTitle($val, array $opt=array())
 *
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findByTitle($val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findMultipleByIds($var, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|CookiebarModel[]|CookiebarModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByTitle($val, array $opt=array())
 */
class GlobalConfigModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_cookie_config';
}
