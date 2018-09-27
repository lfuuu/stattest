<?php

namespace app\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;

/**
 * Class EventQueueIndicator
 *
 * @property int $id
 * @property string $object
 * @property int $object_id
 * @property int $event_queue_id
 * @property string $section
 *
 * @property-read EventQueue $event
 */
class EventQueueIndicator extends ActiveRecord
{
    const SECTION_ACCOUNT_BLOCK = 'account_block';

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
            CreatedAt::class
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(EventQueue::class, ['id' => 'event_queue_id']);
    }
}
