<?php

namespace app\classes\behaviors;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use Yii;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\db\AfterSaveEvent;

class EffectiveVATRate extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'setAccountEffectiveVATRate', // ClientAccount model
            ActiveRecord::EVENT_AFTER_INSERT => 'setContractEffectiveVATRate', // ClientContragent || ClientContract model
            ActiveRecord::EVENT_AFTER_UPDATE => 'setContractEffectiveVATRate', // ClientContragent || ClientContract model
        ];
    }


    /**
     * Рассчитывает эффективную ставку НДС
     *
     * @param AfterSaveEvent $event
     * @throws \Exception
     */
    public function setContractEffectiveVATRate(AfterSaveEvent $event)
    {
        $model = $event->sender;

        $isClientContragent = $model instanceof ClientContragent;
        $isClientContract = $model instanceof ClientContract;

        if (!$isClientContragent && !$isClientContract) {
            return;
        }

        $contracts = [];
        if ($isClientContragent && (isset($event->changedAttributes['country_id']) || isset($event->changedAttributes['tax_regime']))) {
            $contracts = $event->sender->contracts;
        } elseif ($isClientContract && isset($event->changedAttributes['organization_id'])) {
            $contracts = [$event->sender];
        }

        if (!$contracts) {
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            /** @var ClientContract $contract */
            foreach ($contracts as $contract) {
                $contract->resetEffectiveVATRate();
            }

            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Установить эффективную ставку в новом ЛС
     *
     * @param ModelEvent $event
     */
    public function setAccountEffectiveVATRate(ModelEvent $event)
    {
        /** @var ClientAccount $model */
        $model = $event->sender;

        if (!($model instanceof ClientAccount)) {
            return;
        }

        $contract = $model->contract;

        if (!$contract) {
            return;
        }

        $model->effective_vat_rate = ClientContract::dao()->getEffectiveVATRate($contract, $isOrganizationValue);

        // No save. Before save event
    }

}