<?php

namespace app\classes\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\classes\important_events\ImportantEventsRunner;
use app\models\important_events\ImportantEventsProperties;

class ImportantEvents extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'catchedEvent',
        ];
    }

    public function catchedEvent($event)
    {
        if (count($event->sender->propertiesCollection)) {
            Yii::$app->db->createCommand()->batchInsert(
                ImportantEventsProperties::tableName(),
                ['event_id', 'property', 'value'],
                array_map(function($row) use ($event) { $row[0] = $event->sender->id; return $row; }, $event->sender->propertiesCollection)
            )->execute();
        }

        ImportantEventsRunner::start($event->sender);
    }

}