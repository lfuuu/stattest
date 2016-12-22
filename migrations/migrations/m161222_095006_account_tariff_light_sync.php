<?php
use app\classes\uu\model\AccountLogPeriod;

/**
 * Class m161222_095006_account_tariff_light_sync
 */
class m161222_095006_account_tariff_light_sync extends \app\classes\Migration
{
    /**
     * Up
     */
    public function up()
    {
        /** @var AccountLogPeriod $accountLogPeriod */
        foreach (AccountLogPeriod::find()->each() as $accountLogPeriod) {
            echo '. ';

            // эмулировать сохранение, чтобы сработали обработчики
            $accountLogPeriod->trigger(AccountLogPeriod::EVENT_BEFORE_UPDATE);
            $accountLogPeriod->trigger(AccountLogPeriod::EVENT_AFTER_UPDATE);
        }
    }

    /**
     * Down
     */
    public function down()
    {
    }
}