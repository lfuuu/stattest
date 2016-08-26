<?php

use app\models\Virtpbx;

class m160826_154528_add_virtpbx_stat_call extends \app\classes\Migration
{
    public function up()
    {
        $tableName = Virtpbx::tableName();
        $this->addColumn($tableName, 'call_recording_enabled', $this->integer());
        $this->addColumn($tableName, 'faxes_enabled', $this->integer());
    }

    public function down()
    {
        $tableName = Virtpbx::tableName();
        $this->dropColumn($tableName, 'call_recording_enabled');
        $this->dropColumn($tableName, 'faxes_enabled');
    }
}