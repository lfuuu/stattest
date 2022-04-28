<?php

namespace app\classes\behaviors\important_events;

use app\models\EventQueue;
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
     * @throws \app\exceptions\ModelValidationException
     */
    public function registerAddEvent($event)
    {
        $clientAccountId = null;

        /** @var \app\models\ClientContract $contract */
        $contract = $event->sender;
        $clientAccounts = $contract->getAccounts();
        if ($clientAccounts && isset($clientAccounts[0])) {
            $clientAccountId = $clientAccounts[0]->id;
        }

        $userId = Yii::$app->user->id;

        EventQueue::go(EventQueue::CREATE_CONTRACT, [
            'super_client_id' => $contract->super_id,
            'contract_id' => $event->sender->id,
            'user_id' => $userId,
        ]);
    }

    public static function eventAddContract($params)
    {
        foreach (\app\models\ClientAccount::findAll(['contract_id' => $params['contract_id']]) as $clientAccount) {

            $clientAccountId = $clientAccount->id;

            ImportantEvents::create(ImportantEventsNames::EXTEND_ACCOUNT_CONTRACT,
                ImportantEventsSources::SOURCE_STAT,
                $params + [
                    'client_id' => $clientAccountId,
                ]);
        }
    }

    /**
     * @param ModelEvent $event
     * @throws \app\exceptions\ModelValidationException
     */
    public function registerUpdateEvent($event)
    {
        $clientAccountId = null;

        /** @var \app\models\ClientContract $contract */
        $contract = $event->sender;
        $clientAccounts = $contract->getAccounts();
        if ($clientAccounts && isset($clientAccounts[0])) {
            $clientAccountId = $clientAccounts[0]->id;
        }

        $userId = Yii::$app->user->id;

        $changed = array_diff_assoc($event->changedAttributes, $event->sender->attributes);
        $changedCount = count($changed);

        if ($changedCount && $clientAccountId) {
            if (isset($changed['contragent_id'])) {
                ImportantEvents::create(ImportantEventsNames::CONTRACT_TRANSFER,
                    ImportantEventsSources::SOURCE_STAT, [
                        'client_id' => $clientAccountId,
                        'contract_id' => $event->sender->id,
                        'to_contragent_id' => $changed['contragent_id'],
                        'user_id' => $userId,
                        'changed' => implode(', ', array_keys($changed)),
                    ]);
            } else {
                ImportantEvents::create(ImportantEventsNames::ACCOUNT_CONTRACT_CHANGED,
                    ImportantEventsSources::SOURCE_STAT, [
                        'client_id' => $clientAccountId,
                        'contract_id' => $event->sender->id,
                        'user_id' => $userId,
                        'changed' => implode(', ', array_keys($changed)),
                    ]);
            }
        }
    }

}