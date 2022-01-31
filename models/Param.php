<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;

/**
 * Class Param
 *
 * @property string $param
 * @property string $value
 */
class Param extends ActiveRecord
{
    const PI_LIST_LAST_INFO = 'pi_list_last_info'; // информация о последнем импорте
    const NOTIFICATIONS_SWITCH_OFF_DATE = 'notifications_switch_off_date'; // дата отключения оповещений
    const NOTIFICATIONS_SWITCH_ON_DATE = 'notifications_switch_on_date'; // дата включения оповещений
    const NOTIFICATIONS_SCRIPT_ON = 'notification_script_on';
    const NOTIFICATIONS_PERIOD_OFF_MODIFY = '+3 hours'; // через какое время произойдет автоматическое включение оповещений
    const NOTIFICATIONS_LOCK_FILEPATH = '/tmp/yii-check-notification'; // путь к файлу-блокировке работы системы оповещения
    const IS_NEED_RECALC_TT_COUNT = 'is_need_recalc_tt_count';
    const IS_LOG_AAA = 'is_log_aaa';
    const RESOURCE_PARTS = 'resource_parts';

    const IS_OFF = 0; //скрипт (lk/check-notification) выключен
    const IS_ON = 1; //скрипт (lk/check-notification) включен

    // отключение пересчета баланса при редактировании счета
    const DISABLING_RECALCULATION_BALANCE_WHEN_EDIT_BILL = 'disabling_recalculation_balance_when_edit_bill';


    /**
     * @return string
     */
    public static function tableName()
    {
        return 'params';
    }

    /**
     * Установка значения параметра
     *
     * @param string $key
     * @param string $value
     * @param bool $isRawValue
     * @return Param
     * @throws ModelValidationException
     */
    public static function setParam($key, $value, $isRawValue = false)
    {
        $param = self::findOne(['param' => $key]);

        if (!$param) {
            $param = new Param;
            $param->param = $key;
        }

        $param->value = $isRawValue ? $value : json_encode($value);
        if (!$param->save()) {
            throw new ModelValidationException($param);
        }

        return $param;
    }

    /**
     * Получение занчения параметра
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function getParam($key, $defaultValue = null)
    {
        $param = self::findOne(['param' => $key]);

        if (!$param) {
            return $defaultValue;
        }

        return $param->value;
    }

    /**
     * @throws ModelValidationException
     */
    public function setZeroVal()
    {
        $this->value = 0;
        if (!$this->save()) {
            throw new ModelValidationException($this);
        }
    }
}
