<?php

namespace app\classes\behaviors\important_events;

use app\forms\client\ClientAccountOptionsForm;
use app\models\ClientAccountOptions;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;

/**
 * Class ClientAccount
 */
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
        ImportantEvents::create(ImportantEventsNames::NEW_ACCOUNT,
            ImportantEventsSources::SOURCE_STAT,
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
            ImportantEvents::create(ImportantEventsNames::ACCOUNT_CHANGED,
                ImportantEventsSources::SOURCE_STAT,
                [
                    'client_id' => $event->sender->id,
                    'user_id' => Yii::$app->user->id,
                    'changed' => implode(', ', array_keys($changed)),
                ]
            );
        }

        if (isset($event->changedAttributes['voip_disabled'])) {
            $eventName = $event->sender->voip_disabled ?
                ImportantEventsNames::SET_LOCAL_BLOCK :
                ImportantEventsNames::UNSET_LOCAL_BLOCK;

            ImportantEvents::create($eventName,
                ImportantEventsSources::SOURCE_STAT,
                [
                    'client_id' => $event->sender->id,
                    'user_id' => Yii::$app->user->id,
                ]
            );
        }


    }

}
