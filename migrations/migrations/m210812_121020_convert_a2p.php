<?php

use app\classes\Migration;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

/**
 * Class m210812_121020_convert_a2p
 */
class m210812_121020_convert_a2p extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $smsRoutes = \app\models\dictionary\A2pSmsRoute::find()->select('id')->indexBy('name')->column();

        $query = AccountTariff::find()->where(['service_type_id'=> ServiceType::ID_A2P]);

        /** @var AccountTariff $accountTariff */
        foreach ($query->each() as $accountTariff) {

            $routeName = $accountTariff->getParam('route_name', null);

            if ($routeName === null) {
                continue;
            }

            $value = $accountTariff->getParam('route_id', null);

            if ($value !== null) {
                continue;
            }

            if (!isset($smsRoutes[$routeName])) {
                continue;
            }

            $accountTariff->addParam('route_id', $smsRoutes[$routeName]);

            if (!$accountTariff->save()) {
                throw new \app\exceptions\ModelValidationException($accountTariff);
            }
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
