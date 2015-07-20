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

    public function getTariff()
    {
        return $this->hasOne(TariffVoipPackage::className(), ['id' => 'tariff_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'edit_user']);
    }

}

