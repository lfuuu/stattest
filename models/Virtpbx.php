<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $client_id
 * @property int $usage_id
 * @property string $date
 * @property int $use_space
 * @property int $numbers
 * @property
 */
class Virtpbx extends ActiveRecord
{
    public static function tableName()
    {
        return 'virtpbx_stat';
    }
}