<?php

use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffVoipNdcType;

/**
 * Class m171024_160802_change_to_innodb
 */
class m171024_160802_change_to_innodb extends \app\classes\Migration
{
    /**
     * Up
     *
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $tariffTableName = Tariff::tableName();
        $tariffVoipNdcTypeTableName = TariffVoipNdcType::tableName();
        $tariffOrganizationTableName = TariffOrganization::tableName();

        $sql = "ALTER TABLE {$tariffVoipNdcTypeTableName} ENGINE='InnoDB' DEFAULT CHARSET=utf8";
        $this->db->createCommand($sql)->execute();

        $sql = "ALTER TABLE {$tariffOrganizationTableName} ENGINE='InnoDB' DEFAULT CHARSET=utf8";
        $this->db->createCommand($sql)->execute();


        $sql = "DELETE FROM {$tariffVoipNdcTypeTableName} WHERE tariff_id NOT IN (SELECT id FROM {$tariffTableName})";
        $this->db->createCommand($sql)->execute();

        $sql = "DELETE FROM {$tariffOrganizationTableName} WHERE tariff_id NOT IN (SELECT id FROM {$tariffTableName})";
        $this->db->createCommand($sql)->execute();


        $this->addForeignKey($tariffVoipNdcTypeTableName . '_tariff_id', $tariffVoipNdcTypeTableName, 'tariff_id', $tariffTableName, 'id');
        $this->addForeignKey($tariffOrganizationTableName . '_tariff_id', $tariffOrganizationTableName, 'tariff_id', $tariffTableName, 'id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tariffVoipNdcTypeTableName = TariffVoipNdcType::tableName();
        $tariffOrganizationTableName = TariffOrganization::tableName();

        $this->dropForeignKey($tariffVoipNdcTypeTableName . '_tariff_id', $tariffVoipNdcTypeTableName);
        $this->dropForeignKey($tariffOrganizationTableName . '_tariff_id', $tariffOrganizationTableName);
    }
}
