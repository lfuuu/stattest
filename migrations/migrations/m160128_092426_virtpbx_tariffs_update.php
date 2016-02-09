<?php

class m160128_092426_virtpbx_tariffs_update extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(
            'tarifs_virtpbx',
            'ext_did_count',
            'SMALLINT(6) NULL DEFAULT "0" AFTER `overrun_per_gb`'
        );
        $this->addColumn(
            'tarifs_virtpbx',
            'ext_did_monthly_payment',
            'DECIMAL(13,4) NULL DEFAULT "0.0000" AFTER `ext_did_count`'
        );
    }

    public function down()
    {
        $this->dropColumn('tarifs_virtpbx', 'ext_did_count');
        $this->dropColumn('tarifs_virtpbx', 'ext_did_monthly_payment');
    }
}