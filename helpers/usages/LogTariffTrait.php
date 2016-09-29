<?php

namespace app\helpers\usages;

use app\helpers\DateTimeZoneHelper;
use DateTime;
use app\models\LogTarif;

/**
 * Class LogTariffTrait
 * @package app\helpers\usages
 * @property  LogTarif $logTariff
 */
trait LogTariffTrait
{

    /**
     * @param string|null $date
     * @return null|LogTarif
     */
    public function getLogTariff($date = 'now')
    {
        $result =
            LogTarif::find()
                ->andWhere(['service' => self::tableName()])
                ->andWhere(['id_service' => $this->id])
                ->andWhere('id_tarif!=0')
                ->orderBy('date_activation desc, id desc');

        if ($date !== null) {
            $result->andWhere('date_activation <= :date', [':date' => (new DateTime($date))->format(DateTimeZoneHelper::DATE_FORMAT)]);
        }

        return $result->one();
    }

}