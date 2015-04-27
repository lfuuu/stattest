<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\models\User;

class ClientFile extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_files';
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ["id" => "user_id"]);
    }

    public function getContent()
    {
        return ClientAccount::findOne($this->client_id)->fileManager->getContent($this);
    }

    public function getMime()
    {
        return ClientAccount::findOne($this->client_id)->fileManager->getMime($this);
    }
}
