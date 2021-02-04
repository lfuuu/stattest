<?php

namespace app\commands;

use app\helpers\DateTimeZoneHelper;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class SormController extends Controller
{

    public function actionExportRedirects()
    {
        $geted = $this->getGetedRedirects();

        $saved = $this->getSavedRedirects();

        $toAdd = array_diff_key($geted, $saved);
        $toDel = array_diff_key($saved, $geted);

        if ($toAdd) {
            \Yii::$app->db->createCommand()->batchInsert(
                'sorm_redirects',
                ['account_id', 'did', 'type', 'redirect_number'],
                array_map(function ($row) {

                    echo PHP_EOL . date('r') . ' add:';
                    array_walk($row, function ($r, $key) {
                        echo ' ' . $key . ' => ' . $r;
                    });

                    return array_values($row);
                }, $toAdd))->execute();
        }

        if ($toDel) {
            \Yii::$app->db->createCommand()->update(
                'sorm_redirects',
                ['delete_time' => (new \DateTimeImmutable('now'))->format(DateTimeZoneHelper::DATETIME_FORMAT)],
                ['id' => array_values(array_map(function ($row) use ($saved) {

                    echo PHP_EOL . date('r') . ': del:';
                    array_walk($row, function ($r, $key) {
                        echo ' ' . $key . ' => ' . $r;
                    });

                    return $row['id'];
                }, $toDel))]
            )->execute();
        }
    }

    private function getGetedRedirects()
    {
        $data = [];

        $numberAccounts = $this->getVoipAccounts();
        foreach ($this->getRedirects() as $rd) {
            if (isset($numberAccounts[$rd['did']])) {
                $rd['account_id'] = $numberAccounts[$rd['did']];
                $md5 = md5($rd['account_id'] . '|' . $rd['did'] . '|' . $rd['type'] . '|' . $rd['redirect_number']);
                $data[$md5] = $rd;
            }
        }

        return $data;
    }

    private function getVoipAccounts()
    {
        $data = \Yii::$app->db->createCommand('
            select e164 as number, id as account_id from usage_voip where cast(now() as date) between actual_from and actual_to
            union 
            select voip_number as number, id as account_id from uu_account_tariff where tariff_period_id is not null
            
            having number like \'749%\'
        ')->queryAll();

        $data = ArrayHelper::map($data, 'number', 'account_id');

        return $data;
    }

    private function getRedirects()
    {
        $data = \Yii::$app->dbPg->createCommand('select * from sorm_itgrad.get_redirects()')->queryAll();

        return $data;
    }

    private function getSavedRedirects()
    {
        $data = \Yii::$app->db->createCommand('select * from sorm_redirects where delete_time is null')->queryAll();

        return ArrayHelper::index($data, function ($row) {
            return md5($row['account_id'] . '|' . $row['did'] . '|' . $row['type'] . '|' . $row['redirect_number']);
        });
    }

}
