<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class TariffInternet extends ActiveRecord
{
    public static function tableName()
    {
        return 'tarifs_internet';
    }
}