<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\models\User;
use app\classes\FileManager;

class ClientFile extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_files';
    }

    public function attributeLabels()
    {
        return [
            'filename' => 'Файл',
            'companyName' => 'Компания',
            'user' => 'Кто загрузил',
            'comment' => 'Комментарий',
            'ts' => 'Дата загрузки',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ["id" => "user_id"]);
    }

    public function getContent()
    {
        return FileManager::create($this->contract_id)->getContent($this);
    }

    public function getMime()
    {
        return FileManager::create($this->contract_id)->getMime($this);
    }

    public function getContract()
    {
        return $this->hasOne(ClientContract::className(), ['id' => 'contract_id']);
    }
}
