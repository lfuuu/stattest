<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use app\classes\Form;
use app\models\important_events\ImportantEvents;

class UsageVoipPackage extends Behavior
{

    const EVENT_SOURCE = 'stat';

    public function events()
    {
        return [
            Form::EVENT_AFTER_SAVE => 'registerPackageEvent',
        ];
    }

    public function registerPackageEvent($event)
    {
        ImportantEvents::create('new_voip_package', self::EVENT_SOURCE, [
            'usage_id' => $event->sender->usageVoip->id,
            'client_id' => $event->sender->clientAccount->id,
            'user_id' => Yii::$app->user->id,
        ]);
    }

}