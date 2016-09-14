<?php

use app\models\important_events\ImportantEventsNames;

class m160913_121933_important_event_names_comment extends \app\classes\Migration
{
    public function up()
    {
        $tableName = ImportantEventsNames::tableName();
        $this->addColumn($tableName, 'comment', $this->string(255));
    }

    public function down()
    {
        $tableName = ImportantEventsNames::tableName();
        $this->dropColumn($tableName, 'comment');
    }
}