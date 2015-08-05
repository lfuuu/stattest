<?php
namespace app\models\media;

use yii\db\ActiveRecord;
use app\models\User;
use app\models\ClientContract;
use app\classes\media\ClientMedia;

class ClientFiles extends ActiveRecord
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

    public function getContract()
    {
        return $this->hasOne(ClientContract::className(), ['id' => 'contract_id']);
    }

    public function getMediaManager()
    {
        $contract = ClientContract::findOne(['id' => $this->contract_id]);
        return new ClientMedia($contract);
    }

}
