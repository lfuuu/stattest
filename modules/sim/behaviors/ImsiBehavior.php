<?php

namespace app\modules\sim\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\Number;
use app\modules\sim\models\Imsi;
use yii\base\Behavior;
use yii\db\AfterSaveEvent;
use yii\base\Event;
use yii\db\Expression;

class ImsiBehavior extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert', // синхронизация
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate', // синхронизация

            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate', // постобработка
        ];
    }

    public function beforeUpdate(Event $event)
    {
        /** @var Imsi $model */
        $model = $event->sender;
        // Производим синхронизацию IMSI, если прошлое значение не соответствует настоящему
        if (
            !$model->isAttributeChanged('imsi')
            || strpos((string)$model->imsi, '25037') !== 0
            || !$model->msisdn
        ) {
            return;
        }

        /** @var Number $number */
        $number = Number::findOne(['number' => $model->msisdn]);
        if (!$number) {
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

    /**
     * BIL-5200
     * При активации в ЛК сим карты (навесили номер) деактивировать сим карту МТТ
     * Задача состоит в том, что при включении номера в ЛК на IMSI 25037 дергается метод в стат на редактирование этой сим карты.
     * Стат со своей стороны должен сделать проверку, что, если есть такой номер привязанный к IMSI 25042 то деактивировать эту карту.
     * (Или можно проставить параметр is_active = 0 (в таблице billing_uu.sim_imsi этот параметр smallint))
     *
     * @param AfterSaveEvent $event
     * @return string
     * @throws ModelValidationException
     */
    public function afterUpdate(AfterSaveEvent $event)
    {
        /** @var Imsi $imsi */
        $imsi = $event->sender;
        if (!$imsi->msisdn) {
            return;
        }

        if (strpos((string)$imsi->imsi, '25037') !== 0) { // добавляем в Tele2
            return;
        }

        /** @var Imsi $foundedImsi */
        $foundedImsi = Imsi::find()
            ->where([
                'msisdn' => $imsi->msisdn,
                'is_active' => 1
            ])
            ->andWhere('imsi::varchar like \'25042%\'')
            ->one();

        if ($foundedImsi && $foundedImsi->is_active) {
            $foundedImsi->is_active = 0;

            if (!$foundedImsi->save()) {
                throw new ModelValidationException($foundedImsi);
            }

            return true;
        }
    }
}