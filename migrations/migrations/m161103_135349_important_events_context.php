<?php

use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsProperties;

class m161103_135349_important_events_context extends \app\classes\Migration
{
    public function up()
    {
        $importantEventsTableName = ImportantEvents::tableName();

        $this->addColumn($importantEventsTableName, 'context', $this->text());

        $properties = ImportantEventsProperties::find()
            ->select([
                'event_id',
                'data' => new \yii\db\Expression('GROUP_CONCAT(property, "=", value SEPARATOR "#")')
            ])
            ->groupBy('event_id')
            ->asArray();
        $update = [];

        foreach ($properties->each($batchSize = 1000) as $property) {
            $data = explode('#', $property['data']);
            $propertyData = [];
            for ($i=0, $s=count($data); $i<$s; $i++) {
                list ($key, $value) = explode('=', $data[$i]);
                $propertyData[$key] = $value;
                unset($key, $value);
            }
            $update[$property['event_id']] = json_encode($propertyData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            unset($data, $propertyData);
        }

        if (count($update)) {
            foreach ($update as $eventId => $eventContext) {
                $this->update(
                    $importantEventsTableName,
                    ['context' => $eventContext],
                    ['id' => $eventId]
                );
            }
        }
    }

    public function down()
    {
        echo 'm161103_135349_important_events_context cannot be reverted' . PHP_EOL;
        return false;
    }
}