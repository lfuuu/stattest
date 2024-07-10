<?php

namespace app\modules\sorm\classes\redirects;

use app\classes\helpers\DependecyHelper;
use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

class RedirectCollectorDao extends Singleton
{
    public function export($did = null)
    {
        $geted = $this->getGetedRedirects($did);
        $saved = $this->getSavedRedirects($did);

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

    private function getGetedRedirects($did = null)
    {
        $data = [];

        $numberAccounts = $this->getVoipAccounts($did);

        foreach ($this->getRedirects($did) as $rd) {
            if (!($rd['stat_product_id'] ?? false) && !isset($numberAccounts[$rd['did']])) {
                continue;
            }
            $rd['usage_id'] = $rd['stat_product_id'] ?? $numberAccounts[$rd['did']]['usage_id'];
            unset($rd['stat_product_id']);
            $md5 = md5($rd['client_id'] . '|' . $rd['did'] . '|' . $rd['type'] . '|' . $rd['redirect_number']);
            $data[$md5] = $rd;
        }

        return $data;
    }

    private function getSavedRedirects($did = null)
    {
        $data = \Yii::$app->db->createCommand('select * from sorm_redirects where delete_time is null' . ($did ? ' and did = ' . \Yii::$app->db->quoteValue($did) : ''))->queryAll();

        return ArrayHelper::index($data, function ($row) {
            return md5($row['client_id'] . '|' . $row['did'] . '|' . $row['type'] . '|' . $row['redirect_number']);
        });
    }

    private function getRedirects($did = null)
    {
        $data = \Yii::$app->dbPg->createCommand('select * from sorm_itgrad.get_redirects()' . ($did ? ' where did = ' . \Yii::$app->dbPg->quoteValue($did) : ''))->queryAll();

        return $data;
    }

    private function getVoipAccounts($did = null)
    {
        $data = \Yii::$app->db->createCommand('
            select e164 as number, v.id as usage_id, c.id as client_id
            from usage_voip v, clients c
            where cast(now() as date) between v.actual_from and v.actual_to
            and c.client = v.client
            ' . ($did ? ' and e164 =  ' . \Yii::$app->db->quoteValue($did) : '') . '
            
            union
             
            select voip_number as number, id as usage_id, client_account_id as client_id 
            from uu_account_tariff 
            where tariff_period_id is not null
            and service_type_id=2
            ' . ($did ? ' and voip_number = ' . \Yii::$app->db->quoteValue($did) : '') . '
        ')->queryAll();

        $data = ArrayHelper::index($data, 'number');

        return $data;
    }

    private function getRedirectsRanges($did = null)
    {
        $where = $did ? 'where did = ' . \Yii::$app->db->quoteValue($did) : '';
        $sql = <<<SQL
                select * from (
                  select 'add' as action, insert_time as time, r.usage_id, did, redirect_number, type, id
                  from sorm_redirects r
                  union
                  select 'del' as action, delete_time as time, r.usage_id, did, redirect_number, type, id
                  from sorm_redirects r
                  where delete_time is not null
              ) a
                {$where}
                order by time, id
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

    public function group($did = null)
    {
        $loaded = $this->getRedirectsRanges($did);

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
                foreach (['numbers', 'close_time'] as $f) {
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

    public function makeExportEventByDidId($didId)
    {
        $did = \Yii::$app->cache->getOrSet(['get_did_by_did_id', 'didId' => $didId], function () use ($didId) {
            return \Yii::$app->dbPg->createCommand("select sorm_itgrad.get_did_by_did_id(:didId)", ['didId' => $didId])->queryScalar();
        }, DependecyHelper::TIMELIFE_MONTH, (new TagDependency(['tags' => [DependecyHelper::TAG_NUMBER_INFO]])));


        if (!$did) {
            echo '(-) did not found';
            return false;
        }

        echo '(+) did: ' . $did;

        $runTime = DateTimeZoneHelper::getUtcDateTime()
            ->modify('30 second')
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        EventQueue::go(EventQueue::SORM_REDIRECT_EXPORT, ['number' => $did], false, $runTime);
    }
}
