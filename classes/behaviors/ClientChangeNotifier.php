<?php

namespace app\classes\behaviors;

use app\classes\dto\ChangeClientStructureRegistratorDto;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\modules\uu\models\AccountTariff;
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
            if ($isInsert) {
                ChangeClientStructureRegistratorDto::me()->registrChange(ChangeClientStructureRegistratorDto::CONTRACT, $model->id);
                return;
            }

            $attrs = ['business_process_status_id', 'state', 'is_lk_access', 'partner_contract_id', 'is_partner_login_allow', 'account_manager', 'legal_type', ];
            foreach($attrs as $attr) {
                if ($model->isAttributeChanged($attr)) {
                    ChangeClientStructureRegistratorDto::me()->registrChange(ChangeClientStructureRegistratorDto::CONTRACT, $model->id);
                    return;
                }
            }
        } else if ($model instanceof ClientAccount) {
            /** @var ClientAccount $model */
            if ($isInsert) {
                ChangeClientStructureRegistratorDto::me()->registrChange(ChangeClientStructureRegistratorDto::ACCOUNT, $model->id);
                return;
            }

            $attrs = ['is_blocked', 'price_level', 'credit', 'show_in_lk', 'is_active', 'is_bill_pay_overdue', 'is_postpaid'];
            foreach($attrs as $attr) {
                if ($model->isAttributeChanged($attr)) {
                    ChangeClientStructureRegistratorDto::me()->registrChange(ChangeClientStructureRegistratorDto::ACCOUNT, $model->id);
                    return;
                }
            }
        }
    }
}