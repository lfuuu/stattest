<?php

namespace app\classes\behaviors;

use app\classes\adapters\ClientChangedAmqAdapter;
use app\classes\HandlerLogger;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\EventQueue;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class ClientChangeNotifier extends Behavior
{
    //задержка отправки сообщения, для накопления изменений.
    const DELAY = 10;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'setChanged',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'setChanged',
            ActiveRecord::EVENT_AFTER_DELETE => 'setChanged',
        ];
    }

    public function setChanged($event)
    {
        /** @var AccountTariff $model */
        $model = $event->sender;

        $isUpdate = $event->name == ActiveRecord::EVENT_BEFORE_UPDATE;
        $isInsert = $event->name == ActiveRecord::EVENT_AFTER_INSERT;

        $clientAccount = null;
        if ($model instanceof AccountTariff && $isUpdate && $model->isAttributeChanged('tariff_period_id')) {
            $clientAccount = $model->clientAccount;
        } else if ($model instanceof AccountTariffLog) {
            /** @var AccountTariffLog $model */
            $clientAccount = $model->accountTariff->clientAccount;
        } else if ($model instanceof ClientContract && $isUpdate && $model->isAttributeChanged('business_process_status_id')) {
            /** @var ClientContract $model */
            $clientAccount = $model->accounts[0];
        } else if ($model instanceof ClientAccount) {
            if ($isInsert || ($isUpdate && $model->isAttributeChanged('is_blocked'))) {
                $clientAccount = $model;
            }
        }

        if ($clientAccount) {
            $nowStr = (new \DateTime('now'))->format(DateTimeZoneHelper::DATETIME_FORMAT);
            HandlerLogger::me()->add($nowStr .
                ': model: ' . get_class($model) .
                ', id:' . $model->id .
                ($model->hasProperty('account_tariff_id') ? ', account_tariff_id: ' . $model->account_tariff_id : '')
            );

            EventQueue::go(ClientChangedAmqAdapter::EVENT, [
                'event' => ClientChangedAmqAdapter::EVENT,
                'account_id' => $clientAccount->id,
                'client_id' => $clientAccount->super_id,
            ],
                false,
                (new \DateTime('now'))->modify(self::DELAY . ' second')->format(DateTimeZoneHelper::DATETIME_FORMAT)
            );

        }
    }

}