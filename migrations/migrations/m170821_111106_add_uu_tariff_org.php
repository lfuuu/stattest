<?php
use app\models\Country;
use app\models\Organization;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffOrganization;

class m170821_111106_add_uu_tariff_org extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = TariffOrganization::tableName();
        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer(),
            'organization_id' => $this->integer(),
        ]);

        // $this->addForeignKey('organization_id', $tableName, 'organization_id', Organization::tableName(), 'organization_id');
        $this->addForeignKey('tariff_id', $tableName, 'tariff_id', Tariff::tableName(), 'id');

        $tariffTableName = Tariff::tableName();
        $russia = Country::RUSSIA;

        foreach ([Organization::MCN_TELECOM, Organization::MCN_TELECOM_RETAIL] as $organizationId) {
            $sql = <<<SQL
            INSERT INTO {$tableName} (tariff_id, organization_id)
            SELECT 
                id AS tariff_id,
                {$organizationId} AS organization_id
            FROM {$tariffTableName}
            WHERE country_id = {$russia}
SQL;
            $this->execute($sql);
        }

        foreach ([Organization::TEL2TEL_KFT, Organization::TEL2TEL_GMBH] as $organizationId) {
            $sql = <<<SQL
            INSERT INTO {$tableName} (tariff_id, organization_id)
            SELECT 
                id AS tariff_id,
                {$organizationId} AS organization_id
            FROM {$tariffTableName}
            WHERE country_id <> {$russia}
SQL;
            $this->execute($sql);
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = TariffOrganization::tableName();
        $this->dropTable($tableName);
    }
}
