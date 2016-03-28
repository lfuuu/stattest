<?php
namespace app\models;

use yii\db\ActiveRecord;

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
}