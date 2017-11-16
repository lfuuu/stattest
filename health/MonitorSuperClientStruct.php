<?php

namespace app\health;

use app\models\ClientSuper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\InvalidCallException;
use yii\db\Exception;

/**
 * Проверяем выдачу /api/internal/client/get-full-client-struct
 */
class MonitorSuperClientStruct extends Monitor
{
    const ERROR_EXECUTE_VALUE = 999;
    /**
     * Текущее значение
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function getValue()
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $superIds = ClientSuper::dao()->getSuperIds(null, null, null, null, null, $accountTariff->client_account_id);

        $startTime = time();
        try {
            if (!ClientSuper::dao()->getSuperClientStructByIds($superIds)) {
                throw new InvalidCallException('Значение не получено');
            }
        } catch (Exception $e) {
            \Yii::error($e);

            return self::ERROR_EXECUTE_VALUE;
        }

        return time() - $startTime;
    }


    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [4, 10, 25];
    }

}