<?php
use app\classes\uu\model\Period;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\classes\uu\model\TariffStatus;
use app\exceptions\ModelValidationException;

/**
 * Class m170303_095220_one_day_test
 */
class m170303_095220_one_day_test extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $testTariffs = Tariff::findAll(['tariff_status_id' => [TariffStatus::ID_TEST, TariffStatus::ID_VOIP_8800_TEST]]);
        foreach ($testTariffs as $testTariff) {
            $tariffPeriods = $testTariff->tariffPeriods;
            /** @var TariffPeriod $tariffPeriod */
            foreach ($tariffPeriods as $tariffPeriod) {
                // всем тестовым периодам установить посуточное списание
                $tariffPeriod->charge_period_id = Period::ID_DAY;
                if (!$tariffPeriod->save()) {
                    throw new ModelValidationException($tariffPeriod);
                }
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
