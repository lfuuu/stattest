<?php

namespace app\classes\behaviors;

use app\exceptions\ModelValidationException;
use app\models\ClientContract;
use app\models\ClientContragent;
use Yii;
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
            ActiveRecord::EVENT_AFTER_INSERT => 'setEffectiveVATRate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'setEffectiveVATRate',
        ];
    }


    /**
     * Рассчитывает эффективную ставку НДС
     *
     * @param AfterSaveEvent $event
     * @throws \Exception
     */
    public function setEffectiveVATRate($event)
    {
        $model = $event->sender;

        $isClientContragent = $model instanceof ClientContragent;
        $isClientContract = $model instanceof ClientContract;

        if (!($isClientContragent || $isClientContract)) {
            throw new \LogicException('Не поддерживаемая модель в поведении');
        }

        $contracts = [];
        if ($isClientContragent && (isset($event->changedAttributes['country_id']) || $event->changedAttributes['tax_regime'])) {
            $contracts = $event->sender->contracts;
        } elseif ($isClientContract && isset($event->changedAttributes['organization_id'])) {
            $contracts = [$event->sender];
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

}