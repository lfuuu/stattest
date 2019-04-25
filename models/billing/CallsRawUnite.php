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
 * @property integer $market_place
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
    const TYPE_RETAIL = 1;
    const TYPE_TRANSIT = 2;
    const TYPE_INTERNAL = 3;
    const TYPE_UNFINISHED = 4;
    const TYPE_NOT_MERGED = 5;

    const TRAFFIC_TYPE_ALL = 1;
    const TRAFFIC_TYPE_CLIENT = 2; // TYPE_RETAIL + TYPE_INTERNAL
    const TRAFFIC_TYPE_OPERATOR = 3; // TYPE_TRANSIT

    public static $types = [
        self::TYPE_RETAIL => 'Розница',
        self::TYPE_TRANSIT => 'Транзитный',
        self::TYPE_INTERNAL => 'Внутренний',
        self::TYPE_UNFINISHED => 'Несостоявшийся',
        self::TYPE_NOT_MERGED => 'Несклееный',
    ];

    public static $trafficTypes = [
        self::TRAFFIC_TYPE_ALL => 'Весь',
        self::TRAFFIC_TYPE_CLIENT => 'Клиентский',
        self::TRAFFIC_TYPE_OPERATOR => 'Операторский',
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
            'market_place' => 'Биржа, к которой принадлежат плечи звонка.',
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
            'nnp_operator_id_a' => 'Оригинация. Расчитанный оператор для плеча.nnp.operator.id',
            'nnp_operator_id_b' => 'Терминация. Расчитанный оператор для плеча.nnp.operator.id',
            'nnp_region_id_a' => 'Оригинация. Расчитанный регион для плеча.nnp.region.id',
            'nnp_region_id_b' => 'Терминация. Расчитанный регион для плеча.nnp.region.id',
            'nnp_country_code_a' => 'Оригинация. Расчитанная страна для плеча.nnp.country.id',
            'nnp_country_code_b' => 'Терминация. Расчитанная страна для плеча.nnp.country.id',
            'nnp_filter_id1_orig' => 'Группа 1 оригинационного плеча.',
            'nnp_filter_id1_term' => 'Группа 1 терминационного плеча.',
            'nnp_filter_id2_orig' => 'Группа 2 оригинационного плеча.',
            'nnp_filter_id2_term' => 'Группа 2 терминационного плеча.',
            'mcn_callid' => 'Уникальный идентификатор звонка',
        ];
    }
}
