<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\classes\Event;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\ClientAccountOptions;
use app\forms\client\ClientAccountOptionsForm;

class ClientAccount extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'eventClientAccountAdd',
            ActiveRecord::EVENT_AFTER_UPDATE => 'eventClientAccountUpdate',
        ];
    }

    /**
     * @param ModelEvent $event
     * @throws \Exception
     */
    public function eventClientAccountAdd($event)
    {
        ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_NEW_ACCOUNT,
            ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT,
            [
                'client_id' => $event->sender->id,
                'user_id' => Yii::$app->user->id,
            ]
        );

        // Сохранение настройки "Язык уведомлений" для Mailer
        $option = (new ClientAccountOptionsForm)
                ->setClientAccountId($event->sender->id)
                ->setOption(ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE)
                ->setValue($event->sender->country->lang);

        if (!$option->save($deleteExisting = false)) {
            Yii::error('Option "' . ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE . '" not saved for client #' . $event->sender->id . ': ' . implode(',', (array)$option->getFirstErrors()) . PHP_EOL);
        }
    }

    /**
     * @param ModelEvent $event
     * @throws \Exception
     */
    public function eventClientAccountUpdate($event)
    {
        $changed = array_diff_assoc($event->changedAttributes, $event->sender->attributes);
        if (count($changed)) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_ACCOUNT_CHANGED,
                ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT,
                [
                    'client_id' => $event->sender->id,
                    'user_id' => Yii::$app->user->id,
                    'changed' => implode(', ', array_keys($changed)),
                ]
            );
        }

        if (isset($event->changedAttributes['voip_disabled'])) {
            $eventName = $event->sender->voip_disabled ?
                ImportantEventsNames::IMPORTANT_EVENT_SET_LOCAL_BLOCK :
                ImportantEventsNames::IMPORTANT_EVENT_UNSET_LOCAL_BLOCK;

            ImportantEvents::create($eventName,
                ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT,
                [
                    'client_id' => $event->sender->id,
                    'user_id' => Yii::$app->user->id,
                ]
            );
        }


    }

}
