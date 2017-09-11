<?php

use app\models\Country;
use app\modules\nnp\models\NdcType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffStatus;
use app\modules\uu\models\TariffVoipNdcType;

/**
 * Class m170908_141819_add_uu_tariff_ndc_type
 */
class m170908_141819_add_uu_tariff_ndc_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tariffTableName = Tariff::tableName();
        $tariffVoipNdcTypeTableName = TariffVoipNdcType::tableName();

        $countryHungary = Country::HUNGARY;
        $countrySlovakia = Country::SLOVAKIA;

        $status8800 = TariffStatus::ID_VOIP_8800;
        $status8800Test = TariffStatus::ID_VOIP_8800_TEST;
        $statusFmc = TariffStatus::ID_VOIP_FMC;
        $statusMvno = TariffStatus::ID_VOIP_MVNO;

        $ndcTypeFreephone = NdcType::ID_FREEPHONE;
        $ndcTypeMobile = NdcType::ID_MOBILE;
        $ndcTypeGeographic = NdcType::ID_GEOGRAPHIC;

        $this->createTable($tariffVoipNdcTypeTableName, [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer(),
            'ndc_type_id' => $this->integer(),
        ]);

        $this->addForeignKey('tariff_id', $tariffVoipNdcTypeTableName, 'tariff_id', $tariffTableName, 'id');

        // в папке "8-800" проставить тип Freephone
        $sql = <<<SQL
            INSERT INTO {$tariffVoipNdcTypeTableName} (tariff_id, ndc_type_id)
            SELECT id, {$ndcTypeFreephone}
            FROM {$tariffTableName}
            WHERE tariff_status_id IN ({$status8800}, {$status8800Test})
SQL;
        $this->execute($sql);

        // в папке "FMC" и "MVNO" проставить тип Mobile
        $sql = <<<SQL
            INSERT INTO {$tariffVoipNdcTypeTableName} (tariff_id, ndc_type_id)
            SELECT id, {$ndcTypeMobile}
            FROM {$tariffTableName}
            WHERE tariff_status_id IN ({$statusFmc}, {$statusMvno})
SQL;
        $this->execute($sql);

        // для остальных папок проставить тип Geographic
        $sql = <<<SQL
            INSERT INTO {$tariffVoipNdcTypeTableName} (tariff_id, ndc_type_id)
            SELECT id, {$ndcTypeGeographic}
            FROM {$tariffTableName}
            WHERE tariff_status_id NOT IN ({$status8800}, {$status8800Test}, {$statusFmc}, {$statusMvno})
SQL;
        $this->execute($sql);

        // для остальных папок Венгрии /Словакии добавить тип Nomadic
        $sql = <<<SQL
            INSERT INTO {$tariffVoipNdcTypeTableName} (tariff_id, ndc_type_id)
            SELECT id, {$ndcTypeGeographic}
            FROM {$tariffTableName}
            WHERE country_id IN ({$countryHungary}, {$countrySlovakia}) AND tariff_status_id NOT IN ({$status8800}, {$status8800Test}, {$statusFmc}, {$statusMvno})
SQL;
        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tariffVoipNdcTypeTableName = TariffVoipNdcType::tableName();
        $this->dropTable($tariffVoipNdcTypeTableName);
    }
}
