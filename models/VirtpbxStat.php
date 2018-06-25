<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Статистика по использованию юзером ресурсов ВАТС
 *
 * @property int $client_id
 * @property int $usage_id
 * @property string $date
 * @property int $use_space
 * @property int $numbers
 * @property int $ext_did_count
 */
class VirtpbxStat extends ActiveRecord
{
    public static function tableName()
    {
        return 'virtpbx_stat';
    }

    /**
     * Получаем последнее значение статистики
     *
     * @param int $usageId
     * @param string $field
     * @return array|int
     */
    public static function getLastValue($usageId, $field = null)
    {
        $query = self::find()
            ->where(['usage_id' => $usageId])
            ->orderBy(['date' => SORT_DESC])
            ->limit(1);

        $field && $query->select($field);

        return $field ? $query->scalar() : $query->one();
    }
}