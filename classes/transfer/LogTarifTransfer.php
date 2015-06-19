<?php

namespace app\classes\transfer;

use Yii;
use app\models\ClientAccount;
use app\models\LogTarif;

/**
 * Класс переноса тарифов
 * @package app\classes\transfer
 */
abstract class LogTarifTransfer
{

    /**
     * Перенос тарифа
     * @param ServiceTransfer $serviceTransfer - объект переноса услуг
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     */
    public static function process(ServiceTransfer $serviceTransfer, $targetServiceId)
    {
        $logTariff =
            LogTarif::find()
                ->andWhere([
                    'service' => $serviceTransfer->service->serviceType,
                    'id_service' => $serviceTransfer->service->id,
                ])
                ->andWhere('id_tarif != 0')
                ->andWhere('date_activation <= :date', ['date' => $serviceTransfer->getActualDate()])
                ->orderBy('date_activation desc, id desc')
                ->limit(1)
                ->one();

        if ($logTariff === null) {
            return false;
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $targetLogTariff = new $logTariff;
            $targetLogTariff->setAttributes($logTariff->getAttributes(), false);
            unset($targetLogTariff->id);
            $targetLogTariff->date_activation = $serviceTransfer->getActualDate();
            $targetLogTariff->ts = $serviceTransfer->getActivationDatetime();
            $targetLogTariff->id_service = $targetServiceId;

            $targetLogTariff->save();

            $dbTransaction->commit();
        }
        catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

    /**
     * Процесс отмены переноса тарифа
     * @param ServiceTransfer $serviceTransfer - объект переноса услуг
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

        if ($logTariff === null) {
            return false;
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