<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class TariffExtra extends ActiveRecord
{
    public static function tableName()
    {
        return 'tarifs_extra';
    }
}