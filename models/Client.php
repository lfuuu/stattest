<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class Client extends ActiveRecord
{
    public static function tableName()
    {
        return 'clients';
    }
}