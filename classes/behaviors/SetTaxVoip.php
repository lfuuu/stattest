<?php

namespace app\classes\behaviors;

use app\exceptions\ModelValidationException;
use app\models\ClientContract;
use app\models\ClientContragent;
use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\db\AfterSaveEvent;

class SetTaxVoip extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'setTax',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'setTax',
            ClientContract::TRIGGER_RESET_TAX_VOIP => 'setTax',
        ];
    }


    /**
     * Рассчитывает необходимость использования тарифов с НДС или без НДС.
     *
     * @param ModelEvent $event
     * @throws \Exception
     */
    public function setTax($event)
    {
        $model = $event->sender;

        $isClientContragent = $model instanceof ClientContragent;
        $isClientContract = $model instanceof ClientContract;

        if (!($isClientContragent || $isClientContract)) {
            throw new \LogicException('Неподдерживаемая модель в поведении');
        }

        if ($isClientContragent && $model->isAttributeChanged('country_id')) {
            $contracts = $model->contracts;
            $contragent = $model;
        } elseif ($isClientContract && ($model->isAttributeChanged('business_id') || $event->name == ClientContract::TRIGGER_RESET_TAX_VOIP)) {
            $contracts = [$model];
            $contragent = $model->contragent;
        } else {
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            /** @var ClientContract $contract */
            foreach ($contracts as $contract) {
                $contract->resetTaxVoip($contragent);

                if ($contract->isSetVoipWithTax !== null) {
                    foreach ($contract->getAccounts(false) as $account) {

                        if (!$contract->isHistoryVersioning) {
                            $account->detachBehaviors();
                            $account->isHistoryVersioning = false;
                        }

                        if ($account->is_voip_with_tax == $contract->isSetVoipWithTax) {
                            continue;
                        }

                        $account->is_voip_with_tax = $contract->isSetVoipWithTax;
                        if (!$account->save()) {
                            throw new ModelValidationException($account);
                        }
                    }

                    if (!$isClientContract && !$contract->save()) { // сохраняем, если тригер сработал не на договоре
                        throw new ModelValidationException($contract);
                    }
                }
            }

            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}