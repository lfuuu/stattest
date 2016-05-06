<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsSources;

class ClientContract extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'registerAddEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'registerUpdateEvent',
        ];
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function registerAddEvent($event)
    {
        if (($clientAccountId = (int)Yii::$app->request->get('childId'))) {
            ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_EXTEND_ACCOUNT_CONTRACT,
                ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
                    'client_id' => $clientAccountId,
                    'contract_id' => $event->sender->id,
                    'user_id' => Yii::$app->user->id,
                ]);
        }
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\FormValidationException
     */
    public function registerUpdateEvent($event)
    {
        $changed = array_diff_assoc($event->changedAttributes, $event->sender->attributes);
        $changedCount = count($changed);

        if ($changedCount && ($clientAccountId = (int)Yii::$app->request->get('childId'))) {
            if (isset($changed['contragent_id'])) {
                ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_CONTRACT_TRANSFER,
                    ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
                        'client_id' => $clientAccountId,
                        'contract_id' => $event->sender->id,
                        'to_contragent_id' => $changed['contragent_id'],
                        'user_id' => Yii::$app->user->id,
                        'changed' => implode(', ', array_keys($changed)),
                    ]);
            } else {
                ImportantEvents::create(ImportantEventsNames::IMPORTANT_EVENT_ACCOUNT_CONTRACT_CHANGED,
                    ImportantEventsSources::IMPORTANT_EVENT_SOURCE_STAT, [
                        'client_id' => $clientAccountId,
                        'contract_id' => $event->sender->id,
                        'user_id' => Yii::$app->user->id,
                        'changed' => implode(', ', array_keys($changed)),
                    ]);
            }
        }
    }

}