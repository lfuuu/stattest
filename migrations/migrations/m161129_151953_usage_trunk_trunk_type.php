<?php

use app\models\UsageTrunk;

class m161129_151953_usage_trunk_trunk_type extends \app\classes\Migration
{
    public function up()
    {
        $tableName = UsageTrunk::tableName();

        $this->addColumn($tableName, 'trunk_type', $this->integer()->defaultValue(0));
    }

    public function down()
    {
        $tableName = UsageTrunk::tableName();

        $this->dropColumn($tableName, 'trunk_type');
    }
}