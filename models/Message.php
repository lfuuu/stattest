<?php
namespace app\models;

use yii\db\ActiveRecord;

class Message extends ActiveRecord
{
    public static function tableName()
    {
        return 'message';
    }
    
    public function getText()
    {
        return $this->hasOne('app\models\MessageText', ['message_id' => 'id']);
    }
    
    public function getAccount()
    {
        return $this->hasOne('app\models\ClientAccount', ['id' => 'account_id']);
    }
}