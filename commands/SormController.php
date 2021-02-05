<?php

namespace app\commands;

use app\helpers\DateTimeZoneHelper;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
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

        $transaction = null;
        if ($toAdd || $toDel) {
            $transaction = \Yii::$app->db->beginTransaction();
        }

        try {
            if ($toAdd) {

                \Yii::$app->db->createCommand()->batchInsert(
                    'sorm_redirects',
                    ['client_id', 'usage_id', 'did', 'type', 'redirect_number'],
                    array_map(function ($row) {

                        echo PHP_EOL . date('r') . ' add: ';
                        array_walk($row, function ($r, $key) {
                            echo $key . ' => ' . $r . '; ';
                        });

                        $v = [];
                        foreach (['client_id', 'usage_id', 'did', 'type', 'redirect_number'] as $f) {
                            $v[] = $row[$f];
                        }

                        ImportantEvents::create(ImportantEventsNames::REDIRECT_ADD, ImportantEventsSources::SOURCE_STAT, $row);

                        return array_values($v);
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

                        ImportantEvents::create(ImportantEventsNames::REDIRECT_DELETE, ImportantEventsSources::SOURCE_STAT, $row);

                        return $row['id'];
                    }, $toDel))]
                )->execute();
            }

            if ($toAdd || $toDel) {
                $transaction->commit();
//                $transaction->rollBack();
            }

        } catch (\Exception $e) {
            $transaction && $transaction->rollBack();
            throw $e;
        }
    }

    private function getGetedRedirects()
    {
        $data = [];

        $numberAccounts = $this->getVoipAccounts();

        foreach ($this->getRedirects() as $rd) {
//            if ($rd['did'] != 74992133145) {
//                continue;
//            }

            if (isset($numberAccounts[$rd['did']])) {
                $rd['usage_id'] = $numberAccounts[$rd['did']]['usage_id'];
                $md5 = md5($rd['client_id'] . '|' . $rd['did'] . '|' . $rd['type'] . '|' . $rd['redirect_number']);
                $data[$md5] = $rd;
            }
        }

        return $data;
    }

    private function getVoipAccounts()
    {
        $data = \Yii::$app->db->createCommand('
            select e164 as number, v.id as usage_id, c.id as client_id
            from usage_voip v, clients c
            where cast(now() as date) between v.actual_from and v.actual_to
            and c.client = v.client
            
            union
             
            select voip_number as number, id as usage_id, client_account_id as client_id 
            from uu_account_tariff 
            where tariff_period_id is not null
            having number like \'749%\'
        ')->queryAll();

        $data = ArrayHelper::index($data, 'number');

//        $d = [];
//        $d['74992133145'] = $data['74992133145'];

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
            return md5($row['client_id'] . '|' . $row['did'] . '|' . $row['type'] . '|' . $row['redirect_number']);
        });
    }

}
