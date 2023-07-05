<?php

namespace app\modules\sorm\classes\didDvo;

use app\classes\Singleton;
use app\modules\uu\models\AccountTariff;

class DidDvoHelper extends Singleton
{
    private $_regionCache = [];

    public function getRegion($number): ?int
    {
        if (!$this->_regionCache) {
            $c = $this->_loadNumbering();
        }

        return $this->_findRegion($c, $number);
    }

    private function _loadNumbering(): array
    {
        $query = \Yii::$app->dbPg->createCommand('select prefix, number_from, number_to, region_id from sorm_itgrad.phone_numbering')->query();

        $data = [];
        foreach ($query as $i) {
            $regionId = $i['region_id'];
            if (!isset($data[$regionId])) {
                $data[$regionId] = [];
            }

            $data[$regionId][] = [
                'from' => (int)('7' . $i['prefix'] . $i['number_from']),
                'to' => (int)('7' . $i['prefix'] . $i['number_to']),
            ];
        }

        return $data;
    }

    public function getServiceId($accountId, $number): int
    {
        $query = AccountTariff::find()
            ->where(['not', ['tariff_period_id' => null]])
            ->andWhere([
                'voip_number' => $number,
                'client_account_id' => $accountId
            ]);

//        echo PHP_EOL . $query->createCommand()->rawSql;

        return (int)$query->select('id')->scalar();
    }


    private function _findRegion($c, $number): ?int
    {
        $number = (int)$number;

        if (!$number) {
            return null;
        }

        foreach ($c as $regionId => $regionData) {
            foreach ($regionData as $region) {
                if ($region['from'] <= $number && $number <= $region['to']) {
                    return $regionId;
                }
            }
        }

        return null;
    }
}