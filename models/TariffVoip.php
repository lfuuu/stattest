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
    const STATE_PUBLIC = 'public';
    const STATE_TRANSIT = 'transit';
    const STATE_SPECIAL = 'special';
    const STATE_STORE = 'archive';
    const STATE_OPERATOR = 'operator';
    const STATE_TEST = 'test';
    const STATE_7800 = '7800';

    const DEST_RUSSIA = 1;
    const DEST_INTERNATIONAL = 2;
    const DEST_LOCAL_FIXED = 4;
    const DEST_LOCAL_MOBILE = 5;

    const STATE_DEFAULT = self::STATE_PUBLIC;

    const STATUS_TEST = 'test';

    public static $statuses = [
        self::STATE_PUBLIC => 'Публичный',
        self::STATE_SPECIAL => 'Специальный',
        self::STATE_TRANSIT => 'Переходный',
        self::STATE_OPERATOR => 'Оператор',
        self::STATE_TEST => 'Тестовый',
        self::STATE_7800 => '7800',
        self::STATE_STORE => 'Архивный',
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

    public function isTested()
    {
        return $this->status == self::STATUS_TEST;
    }

}
