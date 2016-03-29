<?php

namespace app\helpers\usages;

use DateTime;
use app\models\LogTarif;

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
            $result->andWhere('date_activation <= :date', [':date' => (new DateTime($date))->format('Y-m-d')]);
        }

        return $result->one();
    }

}