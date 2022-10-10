<?php

namespace app\health;

use app\classes\model\ActiveRecord;
use app\models\ClientSuper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\InvalidCallException;
use yii\db\Exception;

/**
 * Проверяем отставание slave-сервера
 */
class BacklogSlaveServer extends Monitor
{
    /**
     * Текущее значение
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function getValue()
    {
        if (!\Yii::$app->isRus()) {
            return 0; // skip check
        }

        try {
            ActiveRecord::setPgTimeout(1000, \Yii::$app->dbPgSlave);

            $value = \Yii::$app
                ->dbPgSlave
                ->createCommand("SELECT ROUND(EXTRACT(EPOCH FROM (NOW() - pg_last_xact_replay_timestamp())))")
                ->queryScalar();

            if ($value === null) {
                throw new InvalidCallException('Репликация не запущена');
            }
        } catch (\Exception $e) {
            \Yii::error($e);

            return self::ERROR_EXECUTE_VALUE;
        }

        return $value;
    }


    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [600, 1200, 3600];
    }

}