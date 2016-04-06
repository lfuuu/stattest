<?php
namespace app\models;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $financial_manager_id

 * @property ClientContragent[] $contragents
 * @property
 */
class ClientSuper extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_super';
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'financial_manager_id' => 'Финансовый менеджер'
        ];
    }

    public function getContragents()
    {
       return $this->hasMany(ClientContragent::className(), ['super_id' => 'id']);
    }

}
