<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class ClientContragent extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contragent';
    }
}