<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\ClientContragentDao;

class ClientContragent extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contragent';
    }

    public function getAccounts()
    {
        return $this->hasMany(ClientAccount::className(), ['contragent_id' => 'id']);
    }

    public function getPerson()
    {
        return $this->hasOne(ClientContragentPerson::className(), ['contragent_id' => 'id']);
    }

    public function dao()
    {
        return ClientContragentDao::me();
    }

    public function saveToAccount()
    {
        return self::dao()->saveToAccount($this);
    }
}
