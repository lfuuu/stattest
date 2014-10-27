<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class TariffVoip extends ActiveRecord
{
    public static function tableName()
    {
        return 'tarifs_voip';
    }
}