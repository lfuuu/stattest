<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * This is the model class for table "calls_raw_unite.calls_raw_unite".
 *
 * @property integer $id_orig
 * @property integer $id_term
 * @property string $connect_time_orig
 * @property string $connect_time_term
 * @property integer $market_place_id
 * @property integer $type
 * @property integer $hub_id_orig
 * @property integer $hub_id_term
 * @property integer $cdr_id_orig
 * @property integer $cdr_id_term
 * @property integer $trunk_id_orig
 * @property integer $trunk_id_term
 * @property integer $account_id_orig
 * @property integer $account_id_term
 * @property integer $trunk_service_id_orig
 * @property integer $trunk_service_id_term
 * @property integer $number_service_id_orig
 * @property integer $number_service_id_term
 * @property integer $src_number
 * @property integer $dst_number
 * @property integer $billed_time_orig
 * @property integer $billed_time_term
 * @property integer $session_time_orig
 * @property integer $session_time_term
 * @property double $rate_orig
 * @property double $rate_term
 * @property double $tax_rate_orig
 * @property double $tax_rate_term
 * @property double $cost_orig
 * @property double $cost_term
 * @property double $tax_cost_orig
 * @property double $tax_cost_term
 * @property boolean $our_orig
 * @property boolean $our_term
 * @property integer $nnp_operator_id_a
 * @property integer $nnp_operator_id_b
 * @property integer $nnp_region_id_a
 * @property integer $nnp_region_id_b
 * @property integer $nnp_country_code_a
 * @property integer $nnp_country_code_b
 * @property integer $nnp_filter_id1_orig
 * @property integer $nnp_filter_id1_term
 * @property integer $nnp_filter_id2_orig
 * @property integer $nnp_filter_id2_term
 * @property string $mcn_callid
 */
class CallsRawUnite extends ActiveRecord
{
    const TYPE_UNFINISHED = 1;
    const TYPE_NOT_MERGED = 2;
    const TYPE_NOT_MERGED_TERM = 3;
    const TYPE_BROKEN = 4;
    const TYPE_RETAIL = 10;
    const TYPE_AST = 11;
    const TYPE_MVNO = 12;
    const TYPE_MVNO_COST = 13;
    const TYPE_TRANSIT = 20;
    const TYPE_OTT = 30;

    const TRAFFIC_TYPE_ALL = 1;
    const TRAFFIC_TYPE_CLIENT = 2; // TYPE_RETAIL + TYPE_AST + TYPE_MVNO + TYPE_OTT
    const TRAFFIC_TYPE_RETAIL = 3; // TYPE_RETAIL + TYPE_AST + TYPE_MVNO
    const TRAFFIC_TYPE_CLIENT_RETAIL = 10; // TYPE_RETAIL
    const TRAFFIC_TYPE_CLIENT_AST = 11; // TYPE_AST
    const TRAFFIC_TYPE_CLIENT_MVNO = 12; // TYPE_MVNO + TYPE_MVNO_COST
    const TRAFFIC_TYPE_OPERATOR = 20; // TYPE_TRANSIT
    const TRAFFIC_TYPE_CLIENT_OTT = 30; // TYPE_OTT

    public static $types = [
        self::TYPE_UNFINISHED => 'Несостоявшийся',
        self::TYPE_NOT_MERGED => 'Несклееный',
        self::TYPE_NOT_MERGED_TERM => 'Несклееный (терм.)',
        self::TYPE_BROKEN => 'Повреждённый',

        self::TYPE_RETAIL => 'Розница',
        self::TYPE_AST => 'Asterisks',
        self::TYPE_MVNO => 'Радиосеть',

        self::TYPE_TRANSIT => 'Транзитный',

        self::TYPE_OTT => 'Мегатранк',
    ];

    public static $trafficTypes = [
        self::TRAFFIC_TYPE_ALL => 'Весь',
        self::TRAFFIC_TYPE_CLIENT => 'Клиентский (10, 11, 12, 13, 30)',
        self::TRAFFIC_TYPE_RETAIL => 'Розничный (10, 11, 12, 13)',
        self::TRAFFIC_TYPE_OPERATOR => 'Операторский (20)',

        self::TRAFFIC_TYPE_CLIENT_RETAIL => 'Клиентский: несортированный (10)',
        self::TRAFFIC_TYPE_CLIENT_AST => 'Клиентский: астериски (11)',
        self::TRAFFIC_TYPE_CLIENT_MVNO => 'Клиентский: радиосеть (12, 13)',
        self::TRAFFIC_TYPE_CLIENT_OTT => 'Клиентский: мегатранки (30)',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'calls_raw_unite.calls_raw_unite';
    }

    /**
     * Returns the database connection
     * 
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id_orig' => 'Оригинация. Ссылка на соответствующую запись calls_raw.',
            'id_term' => 'Терминация. Ссылка на соответствующую запись calls_raw.',
            'connect_time_orig' => 'Оригинация. Время начала разговора (UTC).',
            'connect_time_term' => 'Терминация. Время начала разговора (UTC).',
            'connect_day_key' => 'Ключ для дня начала разговора (UTC).',
            'market_place_id' => 'Биржа, к которой принадлежат плечи звонка.',
            'type' => 'Тип звонка: retail (1)/transit (2)/internal (3)/unfinished (4)/not_merged (5).',
            'hub_id_orig' => 'Оригинация. Хаб плеча.auth.hub.id',
            'hub_id_term' => 'Терминация. Хаб плеча.auth.hub.id',
            'cdr_id_orig' => 'Оригинация. Ссылка на соответствующую запись calls_cdr',
            'cdr_id_term' => 'Терминация. Ссылка на соответствующую запись calls_cdr',
            'trunk_id_orig' => 'Оригинация. Физический транк плеча.auth.trunk',
            'trunk_id_term' => 'Терминация. Физический транк плеча.auth.trunk',
            'account_id_orig' => 'Оригинация. Лицевой счет плеча.billing.clients.id',
            'account_id_term' => 'Терминация. Лицевой счет плеча.billing.clients.id',
            'trunk_service_id_orig' => 'Оригинация. Услуга "транк" плеча.billing.service_trunk.id. Если есть.',
            'trunk_service_id_term' => 'Терминация. Услуга "транк" плеча.billing.service_trunk.id. Если есть.',
            'number_service_id_orig' => 'Оригинация. Услуга "номер" плеча. billing.service_number.id. Если есть.',
            'number_service_id_term' => 'Терминация. Услуга "номер" плеча. billing.service_number.id. Если есть.',
            'src_number' => 'Номер А звонка.',
            'dst_number' => 'Номер Б звонка.',
            'billed_time_orig' => 'Оригинация. Время по которому происходит тарификация. Применяются настройки округления и бесплатных секунд.',
            'billed_time_term' => 'Терминация. Время по которому происходит тарификация. Применяются настройки округления и бесплатных секунд.',
            'session_time_orig' => 'Оригинация. Продолжительность звонка.',
            'session_time_term' => 'Терминация. Продолжительность звонка.',
            'rate_orig' => 'Оригинация. Цена минуты звонка.',
            'rate_term' => 'Терминация. Цена минуты звонка.',
            'tax_rate_orig' => 'Оригинация. НДС для цены минуты звонка.',
            'tax_rate_term' => 'Терминация. НДС для цены минуты звонка.',
            'cost_orig' => 'Оригинация. Общая стоимость звона.На нее меняется баланс.Если есть минуты в пакетах - они уходят из стоимости.',
            'cost_term' => 'Терминация. Общая стоимость звона.На нее меняется баланс.Если есть минуты в пакетах - они уходят из стоимости.',
            'tax_cost_orig' => 'Оригинация. НДС для общей стоимости звонка.',
            'tax_cost_term' => 'Терминация. НДС для общей стоимости звонка.',
            'our_orig' => 'Оригинация. Транк плеча наш. флаг берется из auth.trunk.our_trunk ',
            'our_term' => 'Терминация. Транк плеча наш. флаг берется из auth.trunk.our_trunk ',
            'disconnect_cause_orig' => 'Терминация. Причина завершения вызова. billing.disconnect_cause.cause_id',
            'disconnect_cause_term' => 'Терминация. Причина завершения вызова. billing.disconnect_cause.cause_id',
            'has_asterisk' => 'Один из транков плеч - asterisk',
            'has_mvno' => 'Один из транков плеч - радиосеть',
            'nnp_operator_id_a' => 'Оригинация. Расчитанный оператор для плеча.nnp.operator.id',
            'nnp_operator_id_b' => 'Терминация. Расчитанный оператор для плеча.nnp.operator.id',
            'nnp_region_id_a' => 'Оригинация. Расчитанный регион для плеча.nnp.region.id',
            'nnp_region_id_b' => 'Терминация. Расчитанный регион для плеча.nnp.region.id',
            'nnp_city_id_a' => 'Оригинация. Рассчитанный город для плеча. nnp.city.id',
            'nnp_city_id_b' => 'Терминация. Рассчитанный город для плеча. nnp.city.id',
            'nnp_country_code_a' => 'Оригинация. Расчитанная страна для плеча.nnp.country.id',
            'nnp_country_code_b' => 'Терминация. Расчитанная страна для плеча.nnp.country.id',
            'ndc_type_id_a' => 'Оригинация. Тип номера. nnp.ndc_type.id',
            'ndc_type_id_b' => 'Терминация. Тип номера. nnp.ndc_type.id',
            'nnp_filter_id1_orig' => 'Группа 1 оригинационного плеча.',
            'nnp_filter_id1_term' => 'Группа 1 терминационного плеча.',
            'nnp_filter_id2_orig' => 'Группа 2 оригинационного плеча.',
            'nnp_filter_id2_term' => 'Группа 2 терминационного плеча.',
            'mcn_callid' => 'Уникальный идентификатор звонка',
        ];
    }
}
