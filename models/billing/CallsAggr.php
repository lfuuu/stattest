<?php
namespace app\models\billing;

use app\dao\billing\CallsAggrDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int server_id integer NOT NULL
 * @property string aggr_time
 * @property bool orig boolean,
 * @property int trunk_id integer,
 * @property int account_id integer,
 * @property int trunk_service_id integer,
 * @property int number_service_id integer,
 * @property int destination_id integer,
 * @property bool mob boolean,
 * @property int last_call_id bigint
 * @property int billed_time bigint
 * @property float cost double precision,
 * @property float tax_cost double precision,
 * @property float interconnect_cost double precision,
 * @property int total_calls bigint
 * @property int notzero_calls bigint
 *
 * Связи с другими моделями умышленно не описываю, чтобы не джойнить таблицы. Ибо эта таблица и так огромная, а с джойном будет еще больше тормозить
 * Если надо из id получить название, то см. http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=9142400 (Грид / Column для немногих значений в связанной таблице)
 */
class CallsAggr extends ActiveRecord
{
    public static function tableName()
    {
        return 'calls_aggr.calls_aggr';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return CallsAggrDao::me();
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'server_id' => 'Точка присоединения', // public.server или billing.instance_settings
            'aggr_time' => 'День расчета (UTC)',
            'orig' => 'Оригинация', // оригинация - вход, терминация - выход
            'trunk_id' => 'Транк',
            'account_id' => 'Клиент', // Но вообще он же оператор
            'trunk_service_id' => 'Номер договора', // Услуга транк, billing.service_trunk или usage_trunk
            'number_service_id' => 'Услуга номер',
            'destination_id' => 'Направление', // auth.destination
            'mob' => 'Мобильный', // Стационарный
            'last_call_id' => 'Последний звонок',
            'billed_time' => 'Длительность, сек.',
            'cost' => 'Стоимость без интерконнекта, ¤',
            'tax_cost' => 'Налог, ¤',
            'interconnect_cost' => 'Стоимость интерконнекта, ¤',
            'total_calls' => 'Кол-во звонков',
            'notzero_calls' => 'Кол-во успешных звонков',
        ];
    }
}
