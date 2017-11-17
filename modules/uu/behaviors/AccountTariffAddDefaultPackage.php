<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\Behavior;
use yii\base\Event;


class AccountTariffAddDefaultPackage extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'addDefaultPackage',
        ];
    }

    /**
     *
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     */
    public function addDefaultPackage(Event $event)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        if (!in_array($accountTariff->service_type_id, ServiceType::$packages)) {
            return;
        }

        \app\classes\Event::go(\app\modules\uu\Module::EVENT_ADD_DEFAULT_PACKAGES, [
                'account_tariff_id' => $accountTariff->id,
                'client_account_id' => $accountTariff->client_account_id,
            ]
        );
    }
}
