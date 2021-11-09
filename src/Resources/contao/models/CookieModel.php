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
 * @property integer $pid
 * @property integer $tstamp
 * @property integer $identifier
 * @property integer $sorting
 * @property integer $expireTime
 * @property integer $provider
 * @property string  $title
 * @property string  $token
 * @property string  $type
 * @property string  $vendorId
 * @property string  $vendorUrl
 * @property string  $iframeType
 * @property string  $blockTemplate
 * @property string  $description
 * @property string  $sourceUrl
 * @property integer $sourceLoadingMode
 * @property string  $sourceUrlParameter
 * @property string  $scriptConfirmed
 * @property string  $scriptUnconfirmed
 * @property string  $scriptPosition
 * @property string  $scriptConfig
 * @property boolean $checked
 * @property boolean $published
 *
 * @method static CookieModel|null findById($id, array $opt=array())
 * @method static CookieModel|null findByPk($id, array $opt=array())
 * @method static CookieModel|null findOneBy($col, $val, array $opt=array())
 * @method static CookieModel|null findOneByPid($val, array $opt=array())
 * @method static CookieModel|null findOneByPtable($val, array $opt=array())
 * @method static CookieModel|null findOneByTstamp($val, array $opt=array())
 * @method static CookieModel|null findOneByType($val, array $opt=array())
 * @method static CookieModel|null findOneBySorting($val, array $opt=array())
 * @method static CookieModel|null findOneByExpireTime($val, array $opt=array())
 * @method static CookieModel|null findOneByProvider($val, array $opt=array())
 * @method static CookieModel|null findOneByTitle($val, array $opt=array())
 * @method static CookieModel|null findOneByGoogleId($val, array $opt=array())
 * @method static CookieModel|null findOneByPublished($val, array $opt=array())
 *
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findByPid($val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findByPtable($val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findByTstamp($val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findBySorting($val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findByExpireTime($val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findByProvider($val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findByTitle($val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findByType($val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findByGoogleId($val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findByPublished($val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findMultipleByIds($var, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findBy($col, $val, array $opt=array())
 * @method static \Model\Collection|CookieModel[]|CookieModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTitle($val, array $opt=array())
 * @method static integer countByToken($val, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countByPtable($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByType($val, array $opt=array())
 * @method static integer countByGoogleId($val, array $opt=array())
 * @method static integer countBySorting($val, array $opt=array())
 * @method static integer countByExpireTime($val, array $opt=array())
 * @method static integer countByProvider($val, array $opt=array())
 * @method static integer countByPublished($val, array $opt=array())
 */
class CookieModel extends Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_cookie';

    /**
     * Find published cookies by their PID
     *
     * @param integer $intPid     The parent ID
     * @param array   $arrOptions An optional options array
     *
     * @return \Model\Collection|CookieModel[]|CookieModel|null A collection of models or null if there are no cookies
     */
    public static function findPublishedByPid($intPid, array $arrOptions=array())
    {
        $t = static::$strTable;

        $arrColumns = array(
            "$t.pid=?",
            "$t.published='1'"
        );

        $arrOptions['order'] = "$t.sorting";

        return static::findBy($arrColumns, $intPid, $arrOptions);
    }

    /**
     * Find cookies by their token
     *
     * @param $strToken
     * @param $intRootPageId
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|CookieModel[]|CookieModel|null A collection of models or null if there are no cookies
     */
    public static function findByToken(string $strToken, int $intRootPageId, array $arrOptions=array())
    {
        $t = static::$strTable;

        $objConfig = Cookiebar::getConfigByPage($intRootPageId);
        $objGroups = CookieGroupModel::findByPid($objConfig->id);

        if(!$objGroups)
        {
            return null;
        }

        $arrGroupIds = [];

        foreach ($objGroups as $group)
        {
            $arrGroupIds[] = $group->id;
        }

        $arrColumns = array(
            "$t.token LIKE ?",
            "$t.pid IN (" . implode(",", array_map('intval', $arrGroupIds)) . ")"
        );

        return static::findOneBy($arrColumns, '%' . $strToken . '%', $arrOptions);
    }
}
