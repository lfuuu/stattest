<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class BusinessProcess extends ActiveRecord
{

    const TELECOM_SUPPORT = 1;

    const TELECOM_MAINTENANCE = 1;
    const TELECOM_REPORTS = 16;
    const TELECOM_SALES = 2;

    const INTERNET_SHOP_ORDERS = 3;
    const INTERNET_SHOP_MAINTENANCE = 4;

    const PROVIDER_ORDERS = 5;
    const PROVIDER_MAINTENANCE = 6;

    const PARTNER_MAINTENANCE = 8;

    const INTERNAL_OFFICE = 10;

    const OPERATOR_OPERATORS = 11;
    const OPERATOR_CLIENTS = 12;
    const OPERATOR_INFRASTRUCTURE = 13;
    const OPERATOR_FORMAL = 14;

    const WELLTIME_MAINTENANCE = 15;

    public static function tableName()
    {
        return 'client_contract_business_process';
    }

    public static function getList()
    {
        $arr = self::find()
            ->andWhere(['show_as_status' => '1'])
            ->orderBy('sort')
            ->all();
        return ArrayHelper::map($arr, 'id', 'name');
    }

    public function getBusinessProcessStatuses()
    {
        return $this->hasMany(BusinessProcessStatus::className(), ['business_process_id' => 'id'])->orderBy('sort');
    }

}
