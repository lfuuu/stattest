<?php
namespace app\models;

use yii\db\ActiveRecord;

class ClientContragentPerson extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contragent_person';
    }
}
