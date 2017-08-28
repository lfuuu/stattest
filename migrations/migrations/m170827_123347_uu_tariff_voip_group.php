<?php
use app\modules\uu\models\TariffVoipGroup;

/**
 * Class m170827_123347_uu_tariff_voip_group
 */
class m170827_123347_uu_tariff_voip_group extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        TariffVoipGroup::updateAll(['name' => 'Моб.'], ['id' => TariffVoipGroup::ID_MOB]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        TariffVoipGroup::updateAll(['name' => 'Общ.'], ['id' => TariffVoipGroup::ID_MOB]);
    }
}
