<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\models\EventQueue;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\modules\callTracking\Module;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\Behavior;
use yii\base\Event;
use yii;


class AccountTariffImportantEvents extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
        ];
    }

    /**
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\base\Exception
     */
    public function beforeUpdate(Event $event)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;
        if (
            $accountTariff->service_type_id == ServiceType::ID_CALLTRACKING
            && $accountTariff->isAttributeChanged('calltracking_params')
        ) {
            // При обновлении calltracking_params в услуге добавить в очередь экспорт номера
            EventQueue::go(Module::EVENT_EXPORT_ACCOUNT_TARIFF, [
                'account_tariff_id' => $accountTariff->id,
                'is_active' => (bool)$accountTariff->tariff_period_id,
                'calltracking_params' => $accountTariff->calltracking_params,
            ]);
        }
    }

    /**
     * @param Event $event
     * @throws \yii\db\Exception
     */
    public function afterInsert(Event $event)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        if ($accountTariff->service_type_id != ServiceType::ID_CALLTRACKING) {
            return;
        }

        // создать важное событие
        ImportantEvents::create(ImportantEventsNames::UU_CREATED,
            ImportantEventsSources::SOURCE_STAT, [
                'account_tariff_id' => $accountTariff->id,
                'service_type_id' => $accountTariff->service_type_id,
                'client_id' => $accountTariff->client_account_id,
                'user_id' => Yii::$app->user->id,
            ]);
    }

    /**
     * @param Event $event
     * @throws \yii\db\Exception
     */
    public function afterDelete(Event $event)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        // создать важное событие
        ImportantEvents::create(ImportantEventsNames::UU_DELETED,
            ImportantEventsSources::SOURCE_STAT, [
                'account_tariff_id' => $accountTariff->id,
                'service_type_id' => $accountTariff->service_type_id,
                'client_id' => $accountTariff->client_account_id,
            ]);
    }
}
