<?php
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffStatus;
use app\exceptions\ModelValidationException;

/**
 * Class m170307_120322_clear_test_autoprolongation
 */
class m170307_120322_clear_test_autoprolongation extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $testTariffs = Tariff::findAll([
            'tariff_status_id' => [TariffStatus::ID_TEST, TariffStatus::ID_VOIP_8800_TEST],
        ]);
        foreach ($testTariffs as $testTariff) {
            $testTariff->is_autoprolongation = 0;
            $testTariff->count_of_validity_period = 10;
            if (!$testTariff->save()) {
                throw new ModelValidationException($testTariff);
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
