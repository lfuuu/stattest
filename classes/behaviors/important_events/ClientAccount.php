<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\ClientAccountOptions;
use app\forms\client\ClientAccountOptionsForm;

class ClientAccount extends Behavior
{

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'ClientAccountAddEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'ClientAccountUpdateEvent',
        ];
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function ClientAccountAddEvent($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_NEW_ACCOUNT,
            ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
                'client_id' => $event->sender->id,
                'user_id' => Yii::$app->user->id,
            ]);

        // Сохранение настройки "Язык уведомлений" для Mailer
        $option =
            (new ClientAccountOptionsForm)
                ->setClientAccountId($event->sender->id)
                ->setOption(ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE)
                ->setValue($event->sender->country->lang);
        if (!$option->save($deleteExisting = false)) {
            Yii::error('Option "' . ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE . '" not saved for client #' . $event->sender->id . ': ' . implode(',', (array)$option->getFirstErrors()) . PHP_EOL);
        }
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function ClientAccountUpdateEvent($event)
    {
        $changed = array_diff_assoc($event->changedAttributes, $event->sender->attributes);
        if (count($changed)) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_ACCOUNT_CHANGED,
                ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
                    'client_id' => $event->sender->id,
                    'user_id' => Yii::$app->user->id,
                    'changed' => implode(', ', array_keys($changed)),
                ]);
        }
    }

}