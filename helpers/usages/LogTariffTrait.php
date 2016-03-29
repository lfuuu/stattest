<?php

namespace app\helpers\usages;

use DateTime;
use app\models\LogTarif;

trait LogTariffTrait
{

    /**
     * @param string $date
     * @param boolean $ignoreDate
     * @return null|LogTarif
     */
    public function getLogTariff($date = 'now', $ignoreDate = false)
    {
        $date = (new DateTime($date))->format('Y-m-d H:i:s');

        $result =
            LogTarif::find()
                ->andWhere(['service' => self::tableName()])
                ->andWhere(['id_service' => $this->id])
                ->andWhere('id_tarif!=0')
                ->orderBy('date_activation desc, id desc');

        if (!$ignoreDate) {
            $result->andWhere('date_activation <= :date', [':date' => $date]);
        }

        return $result->one();
    }

}