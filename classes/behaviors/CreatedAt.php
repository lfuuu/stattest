<?php
namespace app\classes\behaviors;

use app\helpers\DateTimeZoneHelper;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class CreatedAt
 */
class CreatedAt extends Behavior
{
    /**
     * События поведения
     *
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'onBeforeInsert',
        ];
    }

    /**
     * Установка метки времени
     */
    public function onBeforeInsert()
    {
        $this->owner->created_at = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
    }
}