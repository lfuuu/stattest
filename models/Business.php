<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property int $id
 * @property string $name
 * @property int $sort
 * @property
 */
class Business extends ActiveRecord
{
    const TELEKOM = 2;
    const OPERATOR = 3;
    const PROVIDER = 4;
    const INTERNET_SHOP = 5;
    const INTERNAL_OFFICE = 6;
    const PARTNER = 7;
    const WELLTIME = 8;

    public static function tableName()
    {
        return 'client_contract_business';
    }

    public static function getList()
    {
        $arr = self::find()->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function getBusinessProcesses()
    {
        return $this->hasMany(BusinessProcess::className(), ['business_id' => 'id']);
    }
}