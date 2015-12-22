<?php

namespace app\classes\behaviors\important_events;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\important_events\ImportantEvents;

class ClientContract extends Behavior
{

    const EVENT_SOURCE = 'stat';

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'registerAddEvent',
            ActiveRecord::EVENT_AFTER_UPDATE => 'registerUpdateEvent',
        ];
    }

    public function registerAddEvent($event)
    {
        if (($clientAccountId = (int) Yii::$app->request->get('childId'))) {
            ImportantEvents::create('extend_account_contract', self::EVENT_SOURCE, [
                'client_id' => $clientAccountId,
                'contract_id' => $event->sender->id,
                'user_id' => Yii::$app->user->id,
            ]);
        }
    }

    public function registerUpdateEvent($event)
    {
        $changed = array_diff_assoc($event->changedAttributes, $event->sender->attributes);
        $changedCount = count($changed);

        if ($changedCount && ($clientAccountId = (int) Yii::$app->request->get('childId'))) {
            if (isset($changed['contragent_id'])) {
                ImportantEvents::create('contract_transfer', self::EVENT_SOURCE, [
                    'client_id' => $clientAccountId,
                    'contract_id' => $event->sender->id,
                    'to_contragent_id' => $changed['contragent_id'],
                    'user_id' => Yii::$app->user->id,
                    'changed' => implode(', ' , array_keys($changed)),
                ]);
            }
            else {
                ImportantEvents::create('account_contract_changed', self::EVENT_SOURCE, [
                    'client_id' => $clientAccountId,
                    'contract_id' => $event->sender->id,
                    'user_id' => Yii::$app->user->id,
                    'changed' => implode(', ' , array_keys($changed)),
                ]);
            }
        }
    }

}