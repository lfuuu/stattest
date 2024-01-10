<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\models\SyncPostgres;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ResourceModel;
use yii\base\Behavior;
use yii\base\Event;


class AccountTariffResourceLogSyncBiller extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'check',
        ];
    }

    /**
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\base\Exception
     */
    public function check(Event $event)
    {
        /** @var $sender AccountTariffResourceLog */
        $sender = $event->sender;

        if (!($sender instanceof AccountTariffResourceLog)) {
            return;
        }

        if (isset(AccountTariffLogicalChangeLog::$transAccountData[$sender->account_tariff_id])) {
            // no need to log on add service
            return;
        }

        if (!ResourceModel::isBillingResource($sender->resource_id)) {
            return;
        }

        SyncPostgres::registerSync(AccountTariff::tableName(), $sender->account_tariff_id);

        return true;
    }
}
