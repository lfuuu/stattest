<?php
namespace app\models;
use yii\db\ActiveRecord;

/**
 * @property int $id

 * @property ClientContragent[] $contragents
 * @property
 */
class ClientSuper extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_super';
    }

    public function getContragents()
    {
       return $this->hasMany(ClientContragent::className(), ['super_id' => 'id']);
    }

    public function getAccountManager()
    {
        return $this->hasOne(User::className(), ['user' => 'account_manager']);
    }

}
