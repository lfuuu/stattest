<?php
namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\classes\Event;

class SetAdminContact extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'onAfterUpdate',
        ];
    }

    public function onAfterUpdate($event)
    {
        if ($event->changedAttributes)
        {
            if (isset($event->changedAttributes["admin_contact_id"]))
            {
                Event::go("admin_changed", ["account_id" => $event->sender->id]);
            }
        }
    }
}
