<?php
namespace app\models;

use yii\db\ActiveRecord;

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
    const NOTIFICATIONS_PERIOD_OFF_MODIFY = '+3 hours'; // через какое время произойдет автоматическое включение оповещений
    const NOTIFICATIONS_LOCK_FILEPATH = '/tmp/yii-check-notification'; // путь к файлу-блокировке работы системы оповещения

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
     * @return bool
     */
    public static function setParam($key, $value)
    {
        $param = self::findOne(['param' => $key]);

        if (!$param) {
            $param = new Param;
            $param->param = $key;
        }

        $param->value = json_encode($value);
        return $param->save();
    }
}
