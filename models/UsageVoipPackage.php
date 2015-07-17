<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 *
 * @property Region $connectionPoint
 * @property
 */
class UsageVoipPackage extends ActiveRecord
{

    public static function tableName()
    {
        return 'usage_voip_package';
    }

    public function beforeSave($query)
    {
        $this->edit_user = \Yii::$app->user->id;
        $this->edit_time = date('Y.m.d H:i:s');

        return parent::beforeSave($query);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffVoipPackage::className(), ['id' => 'tariff_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'edit_user']);
    }

}

