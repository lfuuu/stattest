<?php

namespace app\modules\sorm\models\SipDevice;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\AccountTariff;

/**
 * Class SipDeviceReduced
 *
 * @property string $account_id
 * @property string $region_id
 * @property string $did
 * @property string $service_id
 * @property string $sip_login
 * @property string $activate_dt
 * @property string $expire_dt
 */
class Reduced extends ActiveRecord
{
    public ?string $created_at;

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'sorm_sipdevice';
    }

    public function rules()
    {
        $rules = [[[
            'account_id',
            'region_id',
            'did',
            'sip_login',
            'created_at',
        ], 'safe']];

        return $rules;
    }

    public function beforeSave($isInsert)
    {
        if ($isInsert) {
            $this->service_id = $this->getServiceId();
        }

        return parent::beforeSave($isInsert);
    }

    public function getServiceId()
    {
        $query = AccountTariff::find()
            ->where(['client_account_id' => $this->account_id, 'voip_number' => $this->did])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->select('id');

        $query2 = clone $query;

        $query->andWhere(['NOT', ['tariff_period_id' => null]]);

        $serviceId = $query->select('id')->scalar();

        if ($serviceId) {
            return $serviceId;
        }

        return $query2->scalar() ?: 0;
    }

}
