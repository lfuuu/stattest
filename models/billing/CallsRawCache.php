<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property string $cr1_connect_time
 * @property integer $cr1_number_of_calls
 * @property integer $cr1_billed_time
 * @property integer $cr1_disconnect_cause
 * @property double $cr1_cost
 * @property double $cr1_rate
 * @property integer $cr1_server_id
 * @property integer $cr1_trunk_id
 * @property integer $cr1_trunk_service_id
 * @property integer $cr1_nnp_operator_id
 * @property integer $cr1_nnp_country_code
 * @property integer $cr1_nnp_region_id
 * @property integer $cr1_nnp_city_id
 * @property integer $cr1_account_id
 * @property integer $dst_nr_ndc_type_id
 * @property integer $cr2_billed_time
 * @property integer $cr2_disconnect_cause
 * @property double $cr2_cost
 * @property double $cr2_rate
 * @property integer $cr2_server_id
 * @property integer $cr2_trunk_id
 * @property integer $cr2_trunk_service_id
 * @property integer $cr2_nnp_operator_id
 * @property integer $cr2_nnp_country_code
 * @property integer $cr2_nnp_region_id
 * @property integer $cr2_nnp_city_id
 * @property integer $cr2_account_id
 * @property integer $src_nr_ndc_type_id
 */
class CallsRawCache extends ActiveRecord
{
    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'cr1_connect_time' => 'Время начала разговора (UTC)',
            'cr1_number_of_calls' => 'Количество звонков',
            'session_time' => 'Время тарификации',
            'cr1_disconnect_cause' => 'Код завершения (cr1)',
            'cr1_cost' => 'Стоимость без интерконнекта (cr1)',
            'cr1_rate' => 'Цена минуты без интерконнекта (cr1)',
            'cr1_server_id' => 'Точка присоединения (cr1)',
            'cr1_trunk_id' => 'Транк (cr1)',
            'cr1_trunk_service_id' => 'Номер договора (cr1)',
            'cr1_nnp_operator_id' => 'ННП-оператор (cr1)',
            'cr1_nnp_country_code' => 'ННП-страны (cr1)',
            'cr1_nnp_region_id' => 'ННП-регион (cr1)',
            'cr1_nnp_city_id' => 'ННП-город (cr1)',
            'cr1_account_id' => 'Клиент (cr1)',
            'cr2_disconnect_cause' => 'Код завершения (cr2)',
            'cr2_cost' => 'Стоимость без интерконнекта (cr2)',
            'cr2_rate' => 'Цена минуты без интерконнекта (cr2)',
            'cr2_server_id' => 'Точка присоединения (cr1)',
            'cr2_trunk_id' => 'Транк (cr2)',
            'cr2_trunk_service_id' => 'Номер договора (cr2)',
            'cr2_nnp_operator_id' => 'ННП-оператор (cr2)',
            'cr2_nnp_country_code' => 'ННП-страны (cr2)',
            'cr2_nnp_region_id' => 'ННП-регион (cr2)',
            'cr2_nnp_city_id' => 'ННП-город (cr2)',
            'cr2_account_id' => 'Клиент (cr2)',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'calls_raw_cache.calls_raw_cache';
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
}
