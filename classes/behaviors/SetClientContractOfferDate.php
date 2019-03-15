<?php

namespace app\classes\behaviors;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientContract;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;

class SetClientContractOfferDate extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_UPDATE => 'setDate',
            ActiveRecord::EVENT_BEFORE_INSERT => 'setDate',
        ];
    }

    /**
     * @param ModelEvent $event
     */
    public function setDate(ModelEvent $event)
    {
        /** @var ClientContract $model */
        $model = $event->sender;
        $newState = $model->state;
        $oldState = $model->getOldAttribute('state');

        if ($newState == ClientContract::STATE_OFFER && $oldState != ClientContract::STATE_OFFER && !$model->offer_date) {
            $model->offer_date = (new \DateTimeImmutable('now'))->format(DateTimeZoneHelper::DATETIME_FORMAT);
        }
    }
}
