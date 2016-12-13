<?php

use app\classes\uu\model\Tariff;

class m161213_104719_add_uu_tariff_tarification extends \app\classes\Migration
{
    public function up()
    {
        $tariffTableName = Tariff::tableName();
        $this->addColumn($tariffTableName, 'voip_tarification_free_seconds', $this->integer()->notNull()->defaultValue(3));
        $this->addColumn($tariffTableName, 'voip_tarification_interval_seconds', $this->integer()->notNull()->defaultValue(60));
        $this->addColumn($tariffTableName, 'voip_tarification_type', $this->integer()->notNull()->defaultValue(2));

        $this->update($tariffTableName, [
            'voip_tarification_free_seconds' => 0,
            'voip_tarification_interval_seconds' => 1,
        ], ['voip_tarificate_id' => 1]); // посекундно

        $this->update($tariffTableName, [
            'voip_tarification_free_seconds' => 3,
            'voip_tarification_interval_seconds' => 1,
        ], ['voip_tarificate_id' => 2]); // посекундно, 5 сек бесплатно

        $this->update($tariffTableName, [
            'voip_tarification_free_seconds' => 0,
            'voip_tarification_interval_seconds' => 60,
        ], ['voip_tarificate_id' => 3]); // поминутно

        $this->update($tariffTableName, [
            'voip_tarification_free_seconds' => 3,
            'voip_tarification_interval_seconds' => 60,
        ], ['voip_tarificate_id' => 4]); // поминутно, 5 сек бесплатно

        $this->dropForeignKey('fk-' . $tariffTableName . '-voip_tarificate_id', $tariffTableName);
        $this->dropColumn($tariffTableName, 'voip_tarificate_id');
        $this->dropTable('uu_tariff_voip_tarificate');
    }

    public function down()
    {
        return false;
    }
}