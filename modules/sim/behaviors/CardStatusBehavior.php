<?php

namespace app\modules\sim\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\Number;
use app\modules\sim\models\Card;
use yii\base\Behavior;
use yii\base\Event;

class CardStatusBehavior extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
        ];
    }

    /**
     * При обновлении сим-карты синхронизировать статус склада с таблицей voip_numbers
     *
     * @param Event $event
     */
    public function beforeUpdate(Event $event)
    {
        /** @var Card $model */
        $model = $event->sender;

        if (!$model->isAttributeChanged('status_id')) {
            return;
        }

        /**
         * Синхронизация статуса склада при обновлении модели Card
         *
         * При создании сим-карты синхронизация происходит в поведении ImsiBehavior
         * @see \app\modules\sim\behaviors\ImsiBehavior
         */
        foreach ($model->imsies as $imsi) {
            // TODO: Список номеров, которые игнорируются при синхронизации
            if ($imsi->msisdn === null || in_array($imsi->msisdn, Number::LIST_SKIPPING)) {
                continue;
            }
            /** @var Number $number */
            if (!$number = Number::findOne(['number' => $imsi->msisdn])) {
                continue;
            }
            $number->warehouse_status_id = $model->status_id;
            if (!$number->save()) {
                throw new ModelValidationException($number);
            }
        }
    }
}