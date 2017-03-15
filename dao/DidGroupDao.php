<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\DidGroup;

/**
 * @method static DidGroupDao me($args = null)
 */
class DidGroupDao extends Singleton
{
    /**
     * Вернуть список красивостей
     *
     * @param bool $isWithEmpty
     * @return string[]
     */
    public static function getBeautyLevelList($isWithEmpty = false)
    {
        $list = DidGroup::$beautyLevelNames;

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }
}
