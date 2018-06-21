<?php

namespace app\modules\sim\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\Number;
use app\modules\sim\models\Imsi;
use yii\base\Behavior;
use yii\db\AfterSaveEvent;
use yii\base\Event;

class ImsiBehavior extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
        ];
    }

    public function beforeUpdate(Event $event)
    {
        /** @var Imsi $model */
        $model = $event->sender;
        // Производим синхронизацию IMSI, если прошлое значение не соответствует настоящему
        if (!$model->isAttributeChanged('imsi')) {
            return;
        }
        /** @var Number $number */
        if (!$model->msisdn || !$number = Number::findOne(['number' => $model->msisdn])) {
            return;
        }
        $number->imsi = $model->imsi;
        if (!$number->save()) {
            throw new ModelValidationException($number);
        }
    }

    /**
     * @param AfterSaveEvent $event
     */
    public function afterInsert(AfterSaveEvent $event)
    {
        /**
         * @var Imsi $model
         * @var Number $number
         */
        $model = $event->sender;
        if (!$model->msisdn || !$number = Number::findOne(['number' => $model->msisdn])) {
            return;
        }
        $number->imsi = $model->imsi;
        // Синхронизируем статус склада сим-карты с моделью Number
        if ($card = $model->card) {
            $number->warehouse_status_id = $card->status_id;
        }
        if (!$number->save()) {
            throw new ModelValidationException($number);
        }
    }
}