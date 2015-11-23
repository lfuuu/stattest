<?php

namespace app\dao;

use Yii;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\UsageTechCpe;

class UsageTechCpeDao extends Singleton
{

    public function getCpeIpStat(array $ips)
    {
        $query = '';
        foreach ($ips as $ip) {
            $query .= ($query ? ',' : '') . 'INET_ATON("' . $ip . '")';
        }

        $query = 'ip_int IN (' . $query . ') AND time300 >= FLOOR(UNIX_TIMESTAMP() / 300) - 3';

        $stat = Yii::$app->db->createCommand("SELECT `ip_int`,`value` FROM `monitor_5min` WHERE " . $query)->queryAll();

        $v = count($ips);

        $P1 = 0; $P2 = 0;
        $C1 = 0; $C2 = 0;

        foreach ($stat as $record) {
            if ($v == 1 || (@$record['ip_int'] & 2) == 0) {
                $P1 += $record['value'] ? 5 : 1;
                $C1++;
            } else {
                $P2 += $record['value'] ? 5 : 1;
                $C2++;
            }
        }

        if (!$C1)
            $P1 = '#808080';
        elseif ($P1 == $C1 * 5)
            $P1 = '#00e000';
        elseif ($P1 > 2 * $C1)
            $P1 = '#c0c000';
        else
            $P1 = '#ff0000';

        if (!$C2)
            $P2 = '#808080';
        elseif ($P2 == $C2 * 5)
            $P2 = '#00e000';
        elseif ($P2 > 2 * $C2)
            $P2 = '#c0c000';
        else
            $P2 = '#ff0000';

        return
            '<div class="ping" style="background-color:' . $P1 . '">&nbsp;</div>' .
            (
                $v == 1
                    ? ''
                    : '<div class="ping2" style="background-color:' . $P2 . '">&nbsp;</div>'
            );
    }

    public function getPossibleToTransfer(ClientAccount $client)
    {
        return
            UsageTechCpe::find()
                ->client($client->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->andWhere(['id_service' => 0])
                ->all();
    }

}