<?php

class m161216_115119_usage_trunk_remove_trunk_type extends \app\classes\Migration
{

    public function up()
    {
        $tableName = \app\models\UsageTrunk::tableName();

        $this->dropColumn($tableName, 'trunk_type');
    }

    public function down()
    {
    }

}