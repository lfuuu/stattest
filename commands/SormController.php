<?php

namespace app\commands;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Address;
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

                        echo PHP_EOL . date('r') . ': add:';
                        array_walk($row, function ($r, $key) {
                            echo ' ' . $key . ' => ' . $r . ';';
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
                            echo ' ' . $key . ' => ' . $r . ';';
                        });

                        $id = $row['id'];
                        unset($row['id']);

                        ImportantEvents::create(ImportantEventsNames::REDIRECT_DELETE, ImportantEventsSources::SOURCE_STAT, $row);

                        return $id;
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
            and service_type_id=2
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

    private function getRedirectsRanges()
    {
        $sql = <<<SQL
                select * from (
                  select 'add' as action, insert_time as time, r.usage_id, did, redirect_number, type, id
                  from sorm_redirects r
                  union
                  select 'del' as action, delete_time as time, r.usage_id, did, redirect_number, type, id
                  from sorm_redirects r
                  where delete_time is not null
              ) a order by time, id
SQL;

        $rs = \Yii::$app->db->createCommand($sql)->queryAll();

        $da = [];
        foreach ($rs as $r) {
            unset($r['id']);

            if (!isset($da[$r['usage_id']])) {
                $da[$r['usage_id']] = [];
            }

            if (!isset($da[$r['usage_id']][$r['did']])) {
                $da[$r['usage_id']][$r['did']] = [];
            }

            if (!isset($da[$r['usage_id']][$r['did']][$r['type']])) {
                $da[$r['usage_id']][$r['did']][$r['type']] = [];
            }


            $d = &$da[$r['usage_id']][$r['did']][$r['type']];


            if ($r['action'] == 'add') {
                if (isset($d[$r['time']])) {
                    $d[$r['time']]['numbers'][] = $r['redirect_number'];
                    continue;
                }

                $numbers = [];

                if ($d) {
                    $keys = array_keys($d);
                    $lastD = &$d[array_pop($keys)];
                    if (!$lastD['close_time']) {
                        $lastD['close_time'] = (new \DateTime($r['time']))->modify('-1 second')->format(DateTimeZoneHelper::DATETIME_FORMAT);
                        $numbers = $lastD['numbers'];
                        unset($lastD);
                    }
                }

                $d[$r['time']] = [
                    'usage_id' => $r['usage_id'],
                    'did' => $r['did'],
                    'type' => $r['type'],
                    'numbers' => array_merge($numbers, [$r['redirect_number']]),
                    'open_time' => $r['time'],
                    'close_time' => null,
                ];

            } else { // action == del

                if (!$d) {
                    continue;
                }

                $keys = array_keys($d);
                $lastD = &$d[array_pop($keys)];

                if (!in_array($r['redirect_number'], $lastD['numbers'])) {
                    continue;
                }

                $numbers = $lastD['numbers'];
                unset($numbers[array_search($r['redirect_number'], $numbers)]);

                if ($lastD['open_time'] == $r['time']) {
                    $lastD['numbers'] = array_values($numbers);
                    continue;
                }

                $lastD['close_time'] = (new \DateTime($r['time']))->modify('-1 second')->format(DateTimeZoneHelper::DATETIME_FORMAT);
                unset($lastD);

                if ($numbers) {
                    $d[$r['time']] = [
                        'usage_id' => $r['usage_id'],
                        'did' => $r['did'],
                        'type' => $r['type'],
                        'numbers' => $numbers,
                        'open_time' => $r['time'],
                        'close_time' => null
                    ];
                }
            }
        }

        $data = [];
        foreach ($da as $a3) {
            foreach ($a3 as $a2) {
                foreach ($a2 as $a1) {
                    foreach ($a1 as $l) {
                        $l['numbers'] = implode(', ', $l['numbers']);
                        $data[] = $l;
                    }
                }
            }
        }

        return $data;
    }

    public function actionGroup()
    {
        $loaded = $this->getRedirectsRanges();

        $loadData = [];
        $loadDk = [];
        foreach ($loaded as $item) {
            $key = md5($item['usage_id'] . '|' . $item['did'] . '|' . $item['type'] . '|' . $item['open_time']);
            $loadData[$key] = $item;
            $loadDk[$key] = md5($item['numbers'] . '|' . $item['close_time']);
        }

        $saved = \Yii::$app->db->createCommand("select r.*, 
            md5(concat(usage_id, '|', did, '|', type, '|', open_time)) as pk_key,
            md5(concat(numbers, '|', coalesce(close_time, ''))) as data_key
        from sorm_redirect_ranges r
        ")->queryAll();

        $savedData = [];
        $savedDk = [];
        foreach ($saved as $item) {
            $savedData[$item['pk_key']] = $item;
            $savedDk[$item['pk_key']] = $item['data_key'];
        }

        $toAdd = array_diff_key($loadData, $savedData);
        $toChange = array_diff($savedDk, $loadDk);
        $toChange2 = array_diff($loadDk, $savedDk);

        $toChange = array_merge($toChange, $toChange2);

        if ($toAdd) {
            \Yii::$app->db->createCommand()->batchInsert(
                'sorm_redirect_ranges',
                ['usage_id', 'did', 'type', 'numbers', 'open_time', 'close_time'],
                array_map(function ($row) {

                    echo PHP_EOL . date('r') . ': add:';
                    array_walk($row, function ($r, $key) {
                        echo ' ' . $key . ' => ' . $r . ';';
                    });

                    $v = [];
                    foreach (['usage_id', 'did', 'type', 'numbers', 'open_time', 'close_time'] as $f) {
                        $v[] = $row[$f];
                    }

                    return $v;
                }, $toAdd))->execute();
        }


        if ($toChange) {
            foreach ($toChange as $k => $c) {
                if (!isset($savedData[$k]) || !isset($loadData[$k])) {
                    continue;
                }

                $s = $savedData[$k];
                $l = $loadData[$k];

                $diff = [];
                foreach(['numbers', 'close_time'] as $f) {
                    if ($s[$f] != $l[$f]) {
                        $diff[$f] = $l[$f];
                    }
                }

                $cond = [
                    'usage_id' => $s['usage_id'],
                    'did' => $s['did'],
                    'type' => $s['type'],
                    'open_time' => $s['open_time'],
                ];

                echo PHP_EOL . date('r') . ': upd:';
                $all = $cond + $diff;
                array_walk($all, function ($r, $key) {
                    echo ' ' . $key . ' => ' . $r . ';';
                });

                \Yii::$app->db->createCommand()->update('sorm_redirect_ranges', $diff, $cond)->execute();

            }
        }
    }

    /**
     * Распознование адресов для SORM
     *
     * @return void
     * @throws ModelValidationException
     */
    public function actionAddressRecognition()
    {
        $token = getenv('DADATA_TOKEN');
        $secret = getenv('DADATA_SECRET') ;

        if (!$token || !$secret) {
            throw new \Exception('Empty auth parameters');
        }

        $dadata = new \Dadata\DadataClient($token, $secret);

        /** @var Address $address */
        foreach(Address::find()->where(['state' => 'added'])->all() as $address) {

            if (!$address->address) {
                $address->state = 'need_check';
                $address->save();
                continue;
            }

            if (stripos($address->address, 'а/я') !== false) {
                $address->is_struct = false;
                $address->state = 'checked';
                $address->save();
                continue;
            }



            $result = $dadata->clean("address", $address->address);
            if ($result) {
                $address->json = json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $address->save();
            }

//            continue;
//            $result = json_decode($address->json, true);

            print_r($result);

            $d = [
                'post_code' => $result['postal_code'],
                'country' => $result['country'],
                'district_type' => $result['district_type'],
                'district' => $result['district'],
                'region_type' => $result['region_type'],
                'region' => $result['region'],
                'city_type' => $result['city_type'] ?? $result['settlement_type'] ?? $result['region_type'],
                'city' => $result['city'] ?? $result['settlement'] ?? $result['region'],
                'street_type' => $result['street_type'],
                'street' => $result['street'],
                'house' => $result['house'],
                'housing' => $result['block'],
                'flat_type' => $result['flat_type'],
                'flat' => $result['flat'],
                'unparsed_parts' => $result['unparsed_parts'],
                'state' => 'checked',
            ];

            if ($result['unparsed_parts'] || !$result['postal_code'] || !$d['street'] || !$d['house']) {
                $d['state'] = 'need_check';
            }

            $address->setAttributes($d, false);
            if (!$address->save()) {
                throw new ModelValidationException($address);
            }

            print_r($d);
        }
    }
}
