<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\CallsDao;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use app\modules\nnp\models\Region;
use app\modules\nnp\models\City;
use app\dao\statistics\CallsRawDao;
use app\dao\statistics\CallsRawStatisticDao;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * @property int $server_id integer NOT NULL
 * @property int $id bigint NOT NULL
 * @property bool $orig boolean,
 * @property int $peer_id bigint,
 * @property int $cdr_id bigint,
 * @property string $connect_time timestamp without time zone,
 * @property int $trunk_id integer,
 * @property int $account_id integer,
 * @property int $trunk_service_id integer,
 * @property int $number_service_id integer,
 * @property int $src_number bigint,
 * @property int $dst_number bigint,
 * @property int $billed_time integer,
 * @property float $rate double precision,
 * @property float $cost double precision,
 * @property float $tax_cost double precision,
 * @property float $interconnect_rate double precision,
 * @property float $interconnect_cost double precision,
 * @property int $service_package_id integer,
 * @property int $service_package_stats_id integer,
 * @property int $package_time integer,
 * @property float $package_credit double precision,
 * @property int $destination_id integer,
 * @property int $pricelist_id integer,
 * @property int $prefix bigint,
 * @property int $geo_id integer,
 * @property int $geo_operator_id integer,
 * @property bool $mob boolean,
 * @property int $operator_id integer,
 * @property bool $geo_mob boolean,
 * @property bool $our boolean,
 * @property int $account_version,
 * @property int $stats_nnp_package_minute_id,
 * @property int $nnp_operator_id,
 * @property int $nnp_region_id,
 * @property int $nnp_city_id,
 * @property int $nnp_country_prefix,
 * @property int $nnp_ndc,
 * @property int $trunk_group_id,
 * @property int $nnp_package_minute_id,
 * @property int $nnp_package_price_id,
 * @property int $nnp_package_pricelist_id,
 *
 * @property-read Operator $operator
 * @property-read Region $region
 * @property-read City $city
 * @property-read Pricelist $priceList
 * @property-read PackageMinute $packageMinute
 * @property-read PackagePrice $packagePrice
 * @property-read PackagePricelist $packagePriceList
 */
class CallsRaw extends ActiveRecord
{
    const CALL_RAWS_REPORT_CACHE_KEY = 'call_raws_repor_cache_key';
    // псевдо-поля для \app\models\filter\CallsFilter::searchCost
    public $geo_ids = '';

    public $calls_count = '';
    public $billed_time_sum = '';

    public $rate_with_interconnect = '';

    public $cost_sum = '';
    public $interconnect_cost_sum = '';
    public $cost_with_interconnect_sum = '';

    public $asr = '';
    public $acd = '';

    /**
     * Вернуть имена полей
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'server_id' => 'Регион (точка подключения)',
            // public.server или billing.instance_settings
            'id' => 'ID',
            'orig' => 'Оригинация',
            // оригинация - вход, терминация - выход
            //'peer_id' => 'xxx',
            'cdr_id' => 'CDR',
            // call data record
            'connect_time' => 'Время начала разговора (UTC)',
            'trunk_id' => 'Транк',
            'account_id' => 'Клиент',
            // Но вообще он же оператор
            'trunk_service_id' => 'Номер договора',
            // Услуга транк, billing.service_trunk или usage_trunk
            'number_service_id' => 'Услуга номер',
            'src_number' => 'Исходящий №',
            'dst_number' => 'Входящий №',
            'billed_time' => 'Длительность, сек.',
            'rate' => 'Цена минуты без интерконнекта, ¤',
            'cost' => 'Стоимость без интерконнекта, ¤',
            'tax_cost' => 'Налог, ¤',
            'interconnect_rate' => 'Цена минуты интерконнекта, ¤',
            'interconnect_cost' => 'Стоимость интерконнекта, ¤',
            'service_package_id' => 'Пакет в биллинге (?)',
            'service_package_stats_id' => 'Пакет в stat (?)',
            'package_time' => 'Секунд из пакета',
            'package_credit' => 'Перерасход (?)',
            'destination_id' => 'Направление',
            'disconnect_cause' => 'Код завершения',

            'trunk_settings_stats_id' => '',
            'account_version' => 'Версия биллера', // см. ClientAccount::VERSION_BILLER_*
            'stats_nnp_package_minute_id' => 'Потрачено минут пакета',
            'nnp_operator_id' => 'ННП-оператор',
            'nnp_region_id' => 'ННП-регион',
            'nnp_city_id' => 'ННП-город',
            'nnp_country_prefix' => 'ННП-страна',
            'nnp_ndc' => 'ННП-NDC',
            'trunk_group_id' => '',
            'nnp_package_minute_id' => 'УУ-пакет минут',
            'nnp_package_price_id' => 'УУ-пакет прайс',
            'nnp_package_pricelist_id' => 'УУ-пакет прайслист',

            // auth.destination
            'pricelist_id' => 'Прайслист',
            'prefix' => 'Префикс',
            // соответствие префиксу из прайса
            'geo_id' => 'География',
            // География, Локация B-номера
            //'geo_operator_id' => 'xxx',
            'mob' => 'Мобильный',
            // Стационарный
            //'operator_id' => 'Оператор', // deprecated. Надо trunk_id -> mysql.stat.usage_trunk -> postgresql.voip.operator
            'geo_mob' => 'Россвязь мобильные',
            'our' => 'Наш клиент',

            // having
            'calls_count' => 'Кол-во звонков',
            'billed_time_sum' => 'Суммарная длительность, мин.',

            'rate_with_interconnect' => 'Цена минуты с интерконнектом, ¤',

            'cost_sum' => 'Суммарная стоимость без интерконнекта, ¤',
            'interconnect_cost_sum' => 'Суммарная стоимость интерконнекта, ¤',
            'cost_with_interconnect_sum' => 'Суммарная стоимость с интерконнектом, ¤',

            'asr' => 'ASR (Отношение звонков с длительностью ко всем звонкам), %',
            'acd' => 'ACD (Средняя длительность), мин.',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'calls_raw.calls_raw';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    /**
     * @return CallsDao
     * @throws \yii\base\Exception
     */
    public static function dao()
    {
        return CallsDao::me();
    }

    /**
     * @return CallsRawDao
     * @throws \yii\base\Exception
     */
    public static function statisticsDao()
    {
        return CallsRawStatisticDao::me();
    }

    /**
     * @return ActiveQuery
     */
    public function getOperator()
    {
        return $this->hasOne(Operator::class, ['id' => 'nnp_operator_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::class, ['id' => 'nnp_region_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'nnp_city_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPriceList()
    {
        return $this->hasOne(Pricelist::class, ['id' => 'pricelist_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPackageMinute()
    {
        return $this->hasOne(PackageMinute::class, ['id' => 'nnp_package_minute_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePrice()
    {
        return $this->hasOne(PackagePrice::class, ['id' => 'nnp_package_price_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePriceList()
    {
        return $this->hasOne(PackagePricelist::class, ['id' => 'nnp_package_pricelist_id']);
    }

    /**
     * Получаем уникальный ключ отчета
     *
     * @param Query $query
     * @return string
     */
    public static function getCacheKey(Query $query)
    {
        return 'calls_raws_cache_' . md5($query->createCommand(self::getDb())->rawSql);
    }

    /**
     * Получаем потраченное минут пакета
     *
     * @return string
     */
    public function getPackageMinutesText()
    {
        return $this->stats_nnp_package_minute_id .
            ($this->nnp_package_minute_id ? ', ' . 'минуты' : '') .
            ($this->nnp_package_price_id ? ', ' . 'прайс' : '') .
            ($this->nnp_package_pricelist_id ? ', ' . 'прайслист' : '');
    }
}