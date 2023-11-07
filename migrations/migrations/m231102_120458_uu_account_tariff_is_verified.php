<?php

/**
 * Class m231102_120458_uu_account_tariff_is_verified
 */
class m231102_120458_uu_account_tariff_is_verified extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\modules\uu\models\AccountTariff::tableName(), 'is_verified', $this->tinyInteger()->comment('была ли верификация подключенного номера.'));
        $this->addColumn(\app\models\voip\StateServiceVoip::tableName(), 'is_verified', $this->tinyInteger()->comment('была ли верификация подключенного номера.'));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\modules\uu\models\AccountTariff::tableName(), 'is_verified');
        $this->dropColumn(\app\models\voip\StateServiceVoip::tableName(), 'is_verified');
    }
}
