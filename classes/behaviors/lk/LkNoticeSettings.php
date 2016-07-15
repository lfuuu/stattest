<?php
namespace app\classes\behaviors\lk;

use app\classes\Event;
use Yii;
use yii\db\AfterSaveEvent;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class LkNoticeSettings extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'setToMailer',
            ActiveRecord::EVENT_AFTER_UPDATE => 'setToMailer',
        ];
    }

    /**
     * @param AfterSaveEvent $event
     * @return bool
     * @throws \yii\db\Exception
     */
    public function setToMailer(AfterSaveEvent $event)
    {
        Event::go('lk_settings_to_mailer', [
            'client_account_id' => $event->sender->client_id,
        ]);
    }

}