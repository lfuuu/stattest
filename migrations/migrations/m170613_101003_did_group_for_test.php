<?php
use app\models\DidGroup;
use app\modules\uu\models\TariffStatus;

/**
 * Class m170613_101003_did_group_for_test
 */
class m170613_101003_did_group_for_test extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        if (!(DidGroup::findOne(['is_service' => 1]))) {

            $tariffs = [];

            for ($i = 1; $i <= 9; $i++) {
                $tariffs['tariff_status_main' . $i] = TariffStatus::ID_PUBLIC;
                $tariffs['tariff_status_package' . $i] = TariffStatus::ID_PUBLIC;
            }

            $this->insert(DidGroup::tableName(), [
                'name' => 'Служебная (Россия)',
                'country_code' => \app\models\Country::RUSSIA,
                'ndc_type_id' => \app\modules\nnp\models\NdcType::ID_GEOGRAPHIC,
                'is_service' => 1
            ] + $tariffs);
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
