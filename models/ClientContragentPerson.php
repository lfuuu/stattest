<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class ClientContragentPerson extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contragent_person';
    }
}
