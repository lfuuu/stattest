<?php

namespace app\modules\sim\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\Number;
use app\modules\sim\models\Imsi;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\AfterSaveEvent;

class ImsiRelationBehavior extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * @param AfterSaveEvent $event
     */
    public function afterInsert(AfterSaveEvent $event)
    {
        /** @var Imsi $model */
        $model = $event->sender;
        $this->_bindingRelation($model->msisdn, $model->imsi);
    }

    /**
     * @param Event $event
     */
    public function afterDelete(Event $event)
    {
        /** @var Imsi $model */
        $model = $event->sender;
        $this->_bindingRelation($model->msisdn);
    }

    /**
     * Установление / Удаление связи между sim_imsi и voip_numbers
     *
     * @param integer $msisdn
     * @param integer|null $imsi
     */
    private function _bindingRelation($msisdn, $imsi = null)
    {
        // Если номер не указан, то связь устанавливать не надо
        if (!$msisdn) {
            return;
        }
        // Выполяняем поиск по номеру в таблице voip_numbers
        $number = Number::findOne(['number' => $msisdn]);
        if (!$number) {
            return;
        }
        // Устанавливаем / Удаляем связь с номером
        $transaction = Number::getDb()->beginTransaction();
        try {
            $number->imsi = $imsi;
            if (!$number->save()) {
                throw new ModelValidationException($number);
            }
            $transaction->commit();
        } catch (ModelValidationException $e) {
            $transaction->rollBack();
        }
    }
}