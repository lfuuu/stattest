<?php

use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffVoipNdcType;

/**
 * Class m171129_122515_drop_uu_tariff
 */
class m171129_122515_drop_uu_tariff extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tariffTableName = Tariff::tableName();

        $tariffOrganizationTableName = TariffOrganization::tableName();
        $this->dropForeignKey($tariffOrganizationTableName . '_tariff_id', $tariffOrganizationTableName);
        $this->addForeignKey($tariffOrganizationTableName . '_tariff_id', $tariffOrganizationTableName, 'tariff_id', $tariffTableName, 'id', 'CASCADE');

        $tariffVoipNdcTypeTableName = TariffVoipNdcType::tableName();
        $this->dropForeignKey($tariffVoipNdcTypeTableName . '_tariff_id', $tariffVoipNdcTypeTableName);
        $this->addForeignKey($tariffVoipNdcTypeTableName . '_tariff_id', $tariffVoipNdcTypeTableName, 'tariff_id', $tariffTableName, 'id', 'CASCADE');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tariffTableName = Tariff::tableName();

        $tariffOrganizationTableName = TariffOrganization::tableName();
        $this->dropForeignKey($tariffOrganizationTableName . '_tariff_id', $tariffOrganizationTableName);
        $this->addForeignKey($tariffOrganizationTableName . '_tariff_id', $tariffOrganizationTableName, 'tariff_id', $tariffTableName, 'id', 'RESTRICT');

        $tariffVoipNdcTypeTableName = TariffVoipNdcType::tableName();
        $this->dropForeignKey($tariffVoipNdcTypeTableName . '_tariff_id', $tariffVoipNdcTypeTableName);
        $this->addForeignKey($tariffVoipNdcTypeTableName . '_tariff_id', $tariffVoipNdcTypeTableName, 'tariff_id', $tariffTableName, 'id', 'RESTRICT');
    }
}
