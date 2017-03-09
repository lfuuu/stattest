<?php
namespace app\models;

use app\classes\behaviors\CreatedAt;
use yii\db\ActiveRecord;

/**
 * Class EventQueueIndicator
 *
 * @property int $id
 * @property string $object
 * @property int $object_id
 * @property int $event_queue_id
 *
 * @property EventQueue $event
 */
class EventQueueIndicator extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'event_queue_indicator';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            CreatedAt::className()
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(EventQueue::className(), ['id' => 'event_queue_id']);
    }
}
