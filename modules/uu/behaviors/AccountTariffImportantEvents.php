<?php

namespace app\modules\uu\behaviors;

use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\modules\uu\models\AccountTariff;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;


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
        ];
    }

    /**
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function afterInsert(Event $event)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        // создать важное событие
        ImportantEvents::create(ImportantEventsNames::UU_CREATED,
            ImportantEventsSources::SOURCE_STAT, [
                'account_tariff_id' => $accountTariff->id,
                'service_type_id' => $accountTariff->service_type_id,
                'client_id' => $accountTariff->client_account_id,
            ]);
    }

    /**
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \Exception
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
