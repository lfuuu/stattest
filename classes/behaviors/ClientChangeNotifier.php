<?php

namespace app\classes\behaviors;

use app\classes\adapters\ClientChangedAmqAdapter;
use app\classes\ChangeClientStructureRegistrator;
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

        $isInsert = $event->name == ActiveRecord::EVENT_AFTER_INSERT;

        if ($model instanceof ClientContract) {
            /** @var ClientContract $model */
            if ($isInsert || $model->isAttributeChanged('business_process_status_id')) {
                ChangeClientStructureRegistrator::me()->registrChange(ChangeClientStructureRegistrator::CONTRACT, $model->id);
            }
        } else if ($model instanceof ClientAccount) {
            /** @var ClientAccount $model */
            if ($isInsert
                || $model->isAttributeChanged('is_blocked')
                || $model->isAttributeChanged('price_level')
                || $model->isAttributeChanged('credit')
            ) {
                ChangeClientStructureRegistrator::me()->registrChange(ChangeClientStructureRegistrator::ACCOUNT, $model->id);
            }
        }
    }
}