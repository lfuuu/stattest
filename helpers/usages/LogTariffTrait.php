<?php

namespace app\helpers\usages;

use DateTime;
use app\models\LogTarif;

trait LogTariffTrait
{

    /**
     * @param string $date
     * @return null|LogTarif
     */
    public function getLogTariff($date = 'now')
    {
        $date = (new DateTime($date))->format('Y-m-d H:i:s');

        return
            LogTarif::find()
                ->andWhere(['service' => self::tableName()])
                ->andWhere(['id_service' => $this->id])
                ->andWhere('date_activation <= :date', [':date' => $date])
                ->andWhere('id_tarif!=0')
                ->orderBy('date_activation desc, id desc')
                ->limit(1)
                ->one();
    }

}