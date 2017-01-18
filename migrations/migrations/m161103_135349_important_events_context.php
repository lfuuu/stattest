<?php

use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsProperties;

class m161103_135349_important_events_context extends \app\classes\Migration
{
    public function up()
    {
        $importantEventsTableName = ImportantEvents::tableName();

        $this->addColumn($importantEventsTableName, 'context', $this->text());
    }

    public function down()
    {
        echo 'm161103_135349_important_events_context cannot be reverted' . PHP_EOL;
        return false;
    }
}