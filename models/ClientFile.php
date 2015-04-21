<?php
namespace app\models;

use yii\db\ActiveRecord;

class ClientFile extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_files';
    }
}
