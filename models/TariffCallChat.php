<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class TariffCallChat extends ActiveRecord
{
    public static function tableName()
    {
        return 'tarifs_call_chat';
    }
}