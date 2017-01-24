<?php

namespace app\classes\transfer;

use app\exceptions\ModelValidationException;
use app\models\LogTarif;
use Yii;
use yii\base\InvalidValueException;

/**
 * Класс переноса тарифов
 * @package app\classes\transfer
 */
abstract class LogTarifTransfer
{

    /**
     * Перенос тарифа
     *
     * @param ServiceTransfer $serviceTransfer - объект переноса услуг
     * @param int $targetServiceId - лицевой счет на который осуществляется перенос услуги
     * @throws \Exception
     * @throws ModelValidationException
     * @throws \yii\base\InvalidValueException
     */
    public static function process(ServiceTransfer $serviceTransfer, $targetServiceId)
    {
        /** @var LogTarif $logTariff */
        $logTariff = LogTarif::find()
            ->andWhere([
                'service' => $serviceTransfer->service->serviceType,
                'id_service' => $serviceTransfer->service->id,
            ])
            ->andWhere('id_tarif != 0')
            ->andWhere('date_activation <= :date', ['date' => $serviceTransfer->getActualDate()])
            ->orderBy('date_activation desc, id desc')
            ->one();

        if (!($logTariff instanceof LogTarif)) {
            throw new InvalidValueException('Услуга не может быть перенесена, не найден тарифный план');
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            /** @var LogTarif $targetLogTariff */
            $targetLogTariff = new $logTariff;
            $targetLogTariff->setAttributes($logTariff->getAttributes(), false);
            unset($targetLogTariff->id);
            $targetLogTariff->date_activation = $serviceTransfer->getActualDate(); // переносим дату активации как есть
            $targetLogTariff->ts = $serviceTransfer->getActivationDatetime();
            $targetLogTariff->id_service = $targetServiceId;

            if (!$targetLogTariff->save()) {
                throw new ModelValidationException($targetLogTariff);
            }

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

    /**
     * Процесс отмены переноса тарифа
     * @param ServiceTransfer $serviceTransfer - объект переноса услуг
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public static function fallback(ServiceTransfer $serviceTransfer)
    {
        $logTariff =
            LogTarif::find()
                ->andWhere([
                    'service' => $serviceTransfer->service->serviceType,
                    'id_service' => $serviceTransfer->service->next_usage_id
                ])
                ->andWhere('id_tarif != 0')
                ->one();

        if (!($logTariff instanceof LogTarif)) {
            throw new InvalidValueException('Услуга не может быть восстановлена, не найден тарифный план');
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $logTariff->delete();
            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

}