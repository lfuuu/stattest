<?php

namespace app\classes\contragent\importer\lk;

use yii\db\Expression;

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

        $rowLk = \Yii::$app->dbPgLk->createCommand('select * from public.contragent where contragent_id = :id', ['id' => $this->contragentId])->queryOne();

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

        $columns = $this->getTableColumnList();
        $notInListColumns = array_diff(array_keys($rowLk), $columns);
        foreach($notInListColumns as $column) {
            unset($rowLk[$column]);
        }

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

    private function getTableColumnList()
    {
        return \Yii::$app->db->createCommand(
            "SELECT t.COLUMN_NAME
                    FROM information_schema.COLUMNS t
                    WHERE TABLE_SCHEMA='import_dict' and TABLE_NAME='core_contragent'
                    ")->queryColumn();
    }

    private function jsonFix(&$row)
    {
        foreach (['data_response', 'changelog', 'stat_response'] as $f) {
            if (isset($row[$f])) {
                $row[$f] = new Expression(":json", ['json' => $row[$f]]);
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

