<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\LogTarif;
use DateTime;
use DateTimeZone;
use yii\base\InvalidValueException;

abstract class LogTarifTransfer
{

    /**
     * @param ActiveRecord $sourceService
     * @param int $targetServiceId
     * @param string $processedFromDate
     * @throws ModelValidationException
     * @throws InvalidValueException
     */
    public static function process(ActiveRecord $sourceService, $targetServiceId, $processedFromDate)
    {
        /** @var LogTarif $logTariff */
        $logTariff = LogTarif::find()
            ->andWhere([
                'service' => $sourceService->serviceType,
                'id_service' => $sourceService->primaryKey,
            ])
            ->andWhere(['!=', 'id_tarif', 0])
            ->andWhere(['<=', 'date_activation', $processedFromDate])
            ->orderBy([
                'date_activation' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->one();

        if (!($logTariff instanceof LogTarif)) {
            throw new InvalidValueException('Service #' . $sourceService->primaryKey . ' can\'t be processed. Unknown tariff');
        }

        /** @var LogTarif $targetLogTariff */
        $targetLogTariff = new $logTariff;
        $targetLogTariff->setAttributes($logTariff->getAttributes(null, ['id']), false);

        $targetLogTariff->date_activation = $processedFromDate; // переносим дату активации как есть
        $targetLogTariff->ts = (new DateTime($processedFromDate))
            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $targetLogTariff->id_service = $targetServiceId;

        if (!$targetLogTariff->save()) {
            throw new ModelValidationException($targetLogTariff);
        }
    }

}