<?php
namespace app\models;

use yii\db\ActiveRecord;

class ClientContact extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contacts';
    }
}
