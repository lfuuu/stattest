<?php
namespace app\classes\behaviors\lk;

use app\classes\Event;
use app\models\ClientContact;
use app\models\LkNoticeSetting;
use Yii;
use yii\db\AfterSaveEvent;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class LkNoticeSettings
 * @package app\classes\behaviors\lk
 */
class LkNoticeSettings extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'runner',
            ActiveRecord::EVENT_AFTER_UPDATE => 'runner',
        ];
    }

    /**
     * Основная функция запуска пула обработчиков поведения
     *
     * @param AfterSaveEvent $event
     */
    public function runner(AfterSaveEvent $event)
    {
        $this->setToMailer($event);
        $this->linkStatus($event);
    }

    /**
     * @param AfterSaveEvent $event
     * @return bool
     * @throws \yii\db\Exception
     */
    public function setToMailer(AfterSaveEvent $event)
    {
        Event::go(Event::LK_SETTINGS_TO_MAILER, [
            'client_account_id' => $event->sender->client_id,
        ]);
    }

    /**
     * Установка зависимого статуса в контакте
     *
     * @param AfterSaveEvent $event
     */
    public function linkStatus(AfterSaveEvent $event)
    {
        $contact = ClientContact::findOne(['id' => $event->sender->client_contact_id]);

        if (!$contact) {
            return;
        }

        /** @var LkNoticeSetting $lkNotice */
        $lkNotice = $event->sender;
        $contact->is_active = $lkNotice->status && $lkNotice->status === LkNoticeSetting::STATUS_WORK ? 1 : 0;
        $contact->save();
    }

}