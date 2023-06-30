<?php

namespace app\classes\contragent\importer\lk;

use app\classes\Utils;

class CoreLkContragentDbSyncer
{
    private int $contragentId = 0;

    public function __construct($contragentId)
    {
        $this->contragentId = $contragentId;
    }

    public function sync(): bool
    {
        if (!$this->contragentId) {
            return false;
        }

        $rowLk = \Yii::$app->dbPgLk->createCommand('select * from contragent where contragent_id = :id', ['id' => $this->contragentId])->queryOne();

        if (!$rowLk) {
            return false;
        }

        $rowStat = \Yii::$app->db->createCommand('select * from import_dict.core_contragent where contragent_id = :id', ['id' => $this->contragentId])->queryOne() ?: [];

        unset($rowLk['data_response_change_date'], $rowLk['changelog'], $rowLk['created_at'], $rowLk['updated_at']);
        unset($rowStat['data_response_change_date'], $rowStat['changelog'], $rowStat['created_at'], $rowStat['updated_at']);

        array_walk($rowLk, function (&$val) {
            if (is_bool($val)) {
                $val = (int)$val;
            }
        });

        if (!$rowStat) {
            $this->jsonFix($rowLk);
            $this->logApply($rowLk, []);
            \Yii::$app->db->createCommand()->insert('import_dict.core_contragent', $rowLk)->execute();
        } elseif ($rowLk) {
            $diff = array_diff_assoc($rowLk, $rowStat);
            $this->logApply($diff, $rowStat);
            if ($diff) {
                $this->jsonFix($diff);
                \Yii::$app->db->createCommand()->update('import_dict.core_contragent', $diff, ['contragent_id' => $this->contragentId])->execute();
            }
        }

        return true;
    }

    private function jsonFix(&$row)
    {
        foreach (['data_response', 'changelog', 'stat_response'] as $f) {
            if (isset($row[$f])) {
                $row[$f] = $row[$f] != '{}' ? Utils::fromJson($row[$f]) : json_decode('{}');
            }
        }
    }

    private function logApply($diff, $rowStat)
    {
        array_walk($diff, function ($val, $key) use ($rowStat) {
            echo PHP_EOL . $this->contragentId . ': ' . $key ;
            echo ': ' . ($rowStat[$key] ?? '') . ' => ' . $val;
        });
    }
}

