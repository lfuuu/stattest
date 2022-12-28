<?php

namespace app\modules\uu\models\traits;

use app\models\City;
use app\models\Region;
use app\modules\uu\models\TariffPeriod;

trait AccountTariffHistoryTrait
{
    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {

            case 'tariff_period_id':
                if (!$value) {
                    return 'Закрыто';
                }

                if ($tariffPeriod = TariffPeriod::findOne(['id' => $value])) {
                    return $tariffPeriod->tariff->getLink();
                }
                break;

            case 'region_id':
                if ($region = Region::findOne(['id' => $value])) {
                    return $region->getLink();
                }
                break;

            case 'city_id':
                if ($city = City::findOne(['id' => $value])) {
                    return $city->getLink();
                }
                break;

            default:
                return parent::prepareHistoryValue($field, $value);
        }

        return $value;
    }

    /**
     * Какие поля не показывать в исторических данных
     *
     * @param string $action
     * @return string[]
     */
    public static function getHistoryHiddenFields($action)
    {
        return [
            'id',
            'client_account_id',
            'insert_user_id',
            'update_user_id',
            'insert_time',
            'update_time',
            'service_type_id',
            'prev_account_tariff_id',
            'voip_number',
            'vm_elid_id',
            'prev_usage_id',
        ];
    }
}