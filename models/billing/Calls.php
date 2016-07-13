<?php
namespace app\models\billing;

use app\dao\billing\CallsDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int server_id integer NOT NULL
 * @property int id bigint NOT NULL
 * @property bool orig boolean,
 * @property int peer_id bigint,
 * @property int cdr_id bigint,
 * @property string connect_time timestamp without time zone,
 * @property int trunk_id integer,
 * @property int account_id integer,
 * @property int trunk_service_id integer,
 * @property int number_service_id integer,
 * @property int src_number bigint,
 * @property int dst_number bigint,
 * @property int billed_time integer,
 * @property float rate double precision,
 * @property float cost double precision,
 * @property float tax_cost double precision,
 * @property float interconnect_rate double precision,
 * @property float interconnect_cost double precision,
 * @property int service_package_id integer,
 * @property int service_package_stats_id integer,
 * @property int package_time integer,
 * @property float package_credit double precision,
 * @property int destination_id integer,
 * @property int pricelist_id integer,
 * @property int prefix bigint,
 * @property int geo_id integer,
 * @property int geo_operator_id integer,
 * @property bool mob boolean,
 * @property int operator_id integer,
 * @property bool geo_mob boolean,
 * @property bool our boolean,
 *
 * Связи с другими моделями умышленно не описываю, чтобы не джойнить таблицы. Ибо эта таблица и так огромная, а с джойном будет еще больше тормозить
 * Если надо из id получить название, то см. http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=9142400 (Грид / Column для немногих значений в связанной таблице)
 */
class Calls extends ActiveRecord
{
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
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'server_id' => 'Точка присоединения',
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
            'rate' => 'Цена минуты без интерконнекта, у.е.',
            'cost' => 'Стоимость без интерконнекта, у.е.',
            'tax_cost' => 'Налог, у.е.',
            'interconnect_rate' => 'Цена минуты интерконнекта, у.е.',
            'interconnect_cost' => 'Стоимость интерконнекта, у.е.',
            'service_package_id' => 'Пакет в биллинге (?)',
            'service_package_stats_id' => 'Пакет в stat (?)',
            'package_time' => 'Секунд из пакета',
            'package_credit' => 'Перерасход (?)',
            'destination_id' => 'Направление',
            'disconnect_cause' => 'Код завершения',
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

            'rate_with_interconnect' => 'Цена минуты с интерконнектом, у.е.',

            'cost_sum' => 'Суммарная стоимость без интерконнекта, у.е.',
            'interconnect_cost_sum' => 'Суммарная стоимость интерконнекта, у.е.',
            'cost_with_interconnect_sum' => 'Суммарная стоимость с интерконнектом, у.е.',

            'asr' => 'ASR (Отношение звонков с длительностью ко всем звонкам), %',
            'acd' => 'ACD (Средняя длительность), мин.',
        ];
    }

    public static function tableName()
    {
        return 'calls_raw.calls_raw';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return CallsDao::me();
    }

}
