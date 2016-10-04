<?php

use app\models\UsageVoipPackage;

class m161004_094252_usage_voip_packages_transfer extends \app\classes\Migration
{
    public function up()
    {
        $tableName = UsageVoipPackage::tableName();
        $this->addColumn($tableName, 'prev_usage_id', $this->integer(11)->defaultValue(0));
        $this->addColumn($tableName, 'next_usage_id', $this->integer(11)->defaultValue(0));
    }

    public function down()
    {
        $tableName = UsageVoipPackage::tableName();
        $this->dropColumn($tableName, 'prev_usage_id');
        $this->dropColumn($tableName, 'next_usage_id');
    }
}