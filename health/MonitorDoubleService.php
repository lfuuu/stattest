<?php

namespace app\health;

use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\modules\nnp\models\AccountTariffLight;

/**
 * Монитор услуг телефонии с одним номером включенных сейчас 
 */
class MonitorDoubleService extends Monitor
{
    private $_message = '';

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 1, 1];
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        $dids = AccountTariffLight::getDb()->createCommand('
        SELECT t.did
              FROM billing.service_number t
              WHERE now() between activation_dt and expire_dt
              group by did
              having count(*) > 1
              ')->queryColumn();

        $this->_message = implode($dids, ', ');

        return count($dids);
    }

    /**
     * Текстовая интерпритация
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
}