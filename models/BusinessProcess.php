<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\classes\traits\GetListTrait;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class BusinessProcess
 *
 * @property integer $id
 * @property integer $business_id
 * @property string $name
 * @property integer $show_as_status
 * @property integer $sort
 *
 * @property-read Business $business
 */
class BusinessProcess extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const TELECOM_SUPPORT = 1;

    const TELECOM_MAINTENANCE = 1;
    const TELECOM_MAINTENANCE_B2C = 21;
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

    const ITOUTSOURSING_MAINTENANCE = 17;

    const OTT_MAINTENANCE = 18;
    const OTT_REPORTS = 19;
    const OTT_SALES = 20;

    /**
     * Навзание таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'client_contract_business_process';
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param int $businessId
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $businessId = null
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['sort' => SORT_ASC],
            $where = [
                'AND',
                ['show_as_status' => '1'],
                $businessId ? ['business_id' => $businessId] : []
            ]
        );
    }

    /**
     * Список бизнес процессов вместе с названием подразделения
     *
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @return array|string[]
     */
    public static function getListWithBusinessName(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        $businessTable = Business::tableName();
        $businessProcessTable = self::tableName();

        $list = self::find()
            ->joinWith("business {$businessTable}")
            ->select([
                new Expression("concat({$businessTable}.name, ' / ', {$businessProcessTable}.name) as bp_name"),
                "{$businessProcessTable}.id"
            ])
            ->indexBy("id")
            ->orderBy(["{$businessTable}.sort" => SORT_ASC, "{$businessProcessTable}.sort" => SORT_ASC])
            ->column();

        return GetListTrait::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }

    /**
     * @return ActiveQuery
     */
    public function getBusiness()
    {
        return $this->hasOne(Business::class, ['id' => 'business_id']);
    }

    /**
     * Связка со статусами
     *
     * @return ActiveQuery
     */
    public function getBusinessProcessStatuses()
    {
        return $this->hasMany(BusinessProcessStatus::class, ['business_process_id' => 'id'])
            ->orderBy('sort');
    }
}
