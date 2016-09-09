<?php

use app\models\important_events\ImportantEvents;

class m160909_101748_important_events_comment extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ImportantEvents::tableName();

        $this->addColumn($tableName, 'comment', $this->string(256));
    }

    public function down()
    {
        $tableName = ImportantEvents::tableName();

        $this->dropColumn($tableName, 'comment');
    }
}