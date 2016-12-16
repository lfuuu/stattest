<?php

use app\classes\uu\model\Tariff;

class m161215_195000_drop_uu_tariff_tarification extends \app\classes\Migration
{
    public function up()
    {
        $tariffTableName = Tariff::tableName();
        $this->dropColumn($tariffTableName, 'voip_tarification_free_seconds');
        $this->dropColumn($tariffTableName, 'voip_tarification_interval_seconds');
        $this->dropColumn($tariffTableName, 'voip_tarification_type');
    }

    public function down()
    {
        $tariffTableName = Tariff::tableName();
        $this->addColumn($tariffTableName, 'voip_tarification_free_seconds', $this->integer()->notNull()->defaultValue(3));
        $this->addColumn($tariffTableName, 'voip_tarification_interval_seconds', $this->integer()->notNull()->defaultValue(60));
        $this->addColumn($tariffTableName, 'voip_tarification_type', $this->integer()->notNull()->defaultValue(2));
    }
}