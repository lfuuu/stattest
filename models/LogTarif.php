<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class LogTarif extends ActiveRecord
{
    public static function tableName()
    {
        return 'log_tarif';
    }
}