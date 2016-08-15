<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\TariffVoipDao;
use app\models\billing\Pricelist;
use app\models\tariffs\TariffInterface;
use app\helpers\tariffs\TariffVoipHelper;

/**
 * Class TariffVoip
 * @package app\models
 *
 * @property int id
 * @property int country_id
 * @property int connection_point_id
 * @property string name
 * @property string name_short
 * @property float sum_deposit
 * @property float month_line
 * @property float month_number
 * @property float once_line
 * @property float once_number
 * @property string type_countstring
 * @property string status
 * @property string period
 * @property int free_local_min
 * @property int freemin_for_number
 * @property float month_min_payment
 * @property int dest
 * @property string currency_id
 * @property int priceid
 * @property int edit_user
 * @property string edit_time
 * @property int is_clientSelectable
 * @property int tarif_group
 * @property int pricelist_id
 * @property int paid_redirect
 * @property int tariffication_by_minutes
 * @property int tariffication_full_first_minute
 * @property int tariffication_free_first_seconds
 * @property int tmp
 * @property int is_virtual
 * @property int is_testing
 * @property int price_include_vat
 */
class TariffVoip extends ActiveRecord implements TariffInterface
{
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

    public static function dao()
    {
        return TariffVoipDao::me();
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    public function getConnectionPoint()
    {
        return $this->hasOne(Region::className(), ['id' => 'connection_point_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    public function getPricelist()
    {
        return $this->hasOne(Pricelist::className(), ['id' => 'pricelist_id']);
    }

    public function getHelper()
    {
        return new TariffVoipHelper($this);
    }

    /**
     * Тестовй ли тариф
     *
     * По основному алгоритму, тестовый тариф - это тот который, в статусе "test".
     * Но для номеров 7800, отдельная папка.
     * @return bool
     */
    public function isTest()
    {
        return $this->status == self::STATUS_TEST || $this->status == self::STATUS_7800_TEST;
    }

}
