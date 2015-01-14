<?php
namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class CreatedAt extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'onBeforeInsert',
        ];
    }

    public function onBeforeInsert()
    {
        $this->owner->created_at = (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTime::ATOM);
    }
}