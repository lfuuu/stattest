<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\helpers\tariffs\TariffVoipHelper;
use app\models\billing\Pricelist;
use app\models\tariffs\TariffInterface;
use app\modules\nnp\models\NdcType;

/**
 * Class TariffVoip
 * @package app\models
 *
 * @property int $id
 * @property int $country_id
 * @property int $connection_point_id
 * @property string $name
 * @property string $name_short
 * @property float $sum_deposit
 * @property float $month_line
 * @property float $month_number
 * @property float $once_line
 * @property float $once_number
 * @property string $type_countstring
 * @property string $status
 * @property string $period
 * @property int $free_local_min
 * @property int $freemin_for_number
 * @property float $month_min_payment
 * @property int $dest
 * @property string $currency_id
 * @property int $priceid
 * @property int $edit_user
 * @property string $edit_time
 * @property int $is_clientSelectable
 * @property int $tarif_group
 * @property int $pricelist_id
 * @property int $paid_redirect
 * @property int $tariffication_by_minutes
 * @property int $tariffication_full_first_minute
 * @property int $tariffication_free_first_seconds
 * @property int $tmp
 * @property int $is_virtual
 * @property int $is_testing
 * @property int $price_include_vat
 * @property int $ndc_type_id
 */
class TariffVoip extends ActiveRecord implements TariffInterface
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const STATUS_TRANSIT = 'transit';
    const STATUS_OPERATOR = 'operator';
    const STATUS_7800 = '7800';
    const STATUS_7800_TEST = '7800_test';

    const DEST_RUSSIA = 1;
    const DEST_INTERNATIONAL = 2;
    const DEST_LOCAL_FIXED = 4;
    const DEST_LOCAL_MOBILE = 5;

    const STATE_DEFAULT = self::STATUS_PUBLIC;

    public static $statuses = [
        self::STATUS_PUBLIC => 'Публичный',
        self::STATUS_SPECIAL => 'Специальный',
        self::STATUS_TRANSIT => 'Переходный',
        self::STATUS_OPERATOR => 'Оператор',
        self::STATUS_TEST => 'Тестовый',
        self::STATUS_7800 => '7800',
        self::STATUS_7800_TEST => '7800 Тестовый',
        self::STATUS_ARCHIVE => 'Архивный',
    ];

    public static $destinations = [
        self::DEST_RUSSIA => 'Россия',
        self::DEST_INTERNATIONAL => 'Международка',
        self::DEST_LOCAL_FIXED => 'Местные Стационарные',
        self::DEST_LOCAL_MOBILE => 'Местные Мобильные',
    ];

    public static function tableName()
    {
        return 'tarifs_voip';
    }

    public function beforeSave($query)
    {
        $this->edit_user = \Yii::$app->user->id;
        $this->edit_time = date('Y.m.d H:i:s');

        return parent::beforeSave($query);
    }

    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_id']);
    }

    public function getConnectionPoint()
    {
        return $this->hasOne(Region::class, ['id' => 'connection_point_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    public function getPricelist()
    {
        return $this->hasOne(Pricelist::class, ['id' => 'pricelist_id']);
    }

    public function getHelper()
    {
        return new TariffVoipHelper($this);
    }

    /**
     * Тестовй ли тариф
     * По основному алгоритму, тестовый тариф - это тот который, в статусе "test".
     * Но для номеров 7800, отдельная папка.
     *
     * @return bool
     */
    public function isTest()
    {
        return $this->status == self::STATUS_TEST || $this->status == self::STATUS_7800_TEST;
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param int $dest
     * @param bool $priceIncludeVat
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param int $connectingPointId
     * @param string $currencyId
     * @param int $status
     * @param int $ndcTypeId
     * @param int $countryId
     * @param int $tariffId
     * @return string[]
     */
    public static function getList(
        $dest,
        $priceIncludeVat,
        $isWithEmpty = false,
        $connectingPointId = null,
        $currencyId = null,
        $status = null,
        $ndcTypeId = NdcType::ID_GEOGRAPHIC,
        $countryId = null,
        $tariffId = null
    )
    {

        // Тарифы для FREEPHONE для доп.опций брать из точки подключения по умолчанию.
        if ($ndcTypeId == NdcType::ID_FREEPHONE) {
            $country = Country::findOne($countryId);

            if (!$country) {
                throw new \LogicException('Страна не установленна');
            }
            $connectingPointId = $country->default_connection_point_id;

            $dest != self::DEST_LOCAL_FIXED && $ndcTypeId = NdcType::ID_GEOGRAPHIC;
        }

        // для линии без номера брать тарифи из географических номеров, кроме основного тарифа
        if ($ndcTypeId == NdcType::ID_MCN_LINE && $dest != self::DEST_LOCAL_FIXED) {
            $ndcTypeId = NdcType::ID_GEOGRAPHIC;
        }

        $where = [
            'AND',
            [
                'dest' => $dest,
                'price_include_vat' => $priceIncludeVat,
                'ndc_type_id' => $ndcTypeId ?: NdcType::ID_GEOGRAPHIC,
            ],
            $status ? ['status' => $status] : [],
            $connectingPointId ? ['connection_point_id' => $connectingPointId] : [],
            $currencyId ? ['currency_id' => $currencyId] : [],
        ];

        if ($tariffId) {
            $where = ['OR', ['id' => $tariffId], $where];
        }
        
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'CONCAT(name, " (", month_number, " - ", month_line, ") - ", IFNULL((SELECT name FROM regions r WHERE r.id = ' . self::tableName() . '.connection_point_id LIMIT 1), ""))',
            $orderBy = ['status' => SORT_ASC, 'month_min_payment' => SORT_ASC],
            $where
        );
    }
}
