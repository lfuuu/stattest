<?php

namespace app\modules\sorm\classes\sipDevice\behavior;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\modules\sorm\models\SipDevice\StateLog;
use yii\base\Behavior;
use yii\base\Event;

class MakeLogBehavior extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => "registerLog",
            ActiveRecord::EVENT_BEFORE_DELETE => "registerLog",
        ];
    }


    public function registerLog(Event $event)
    {
        $log = new StateLog();
        $log->load($event->sender->getAttributes(), '');
        $log->is_add = (int)($event->name == ActiveRecord::EVENT_AFTER_INSERT);

        if (!$log->save()) {
            throw new ModelValidationException($log);
        }
    }
}