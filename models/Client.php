<?php
namespace app\models;

use app\classes\behaviors\HistoryVersion;
use app\classes\behaviors\HistoryChanges;
use yii\db\ActiveRecord;

class Client extends ActiveRecord
{
    public static function tableName()
    {
        return 'clients';
    }


}
