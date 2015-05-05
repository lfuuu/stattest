<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\models\MessageText;

class Message extends ActiveRecord
{
    public static function tableName()
    {
        return 'message';
    }
    
    public function getText()
    {
        return $this->hasOne(MessageText::className(), ['message_id' => 'id']);
    }
    
    public function getAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'account_id']);
    }
}