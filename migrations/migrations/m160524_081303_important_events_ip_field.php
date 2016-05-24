<?php

use app\models\important_events\ImportantEvents;

class m160524_081303_important_events_ip_field extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(ImportantEvents::tableName(), 'from_ip', 'VARBINARY(39)');
    }

    public function down()
    {
        $this->dropColumn(ImportantEvents::tableName(), 'from_ip');
    }
}