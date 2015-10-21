<?php
namespace app\classes\stats;

use Yii;

class PhoneSales
{

    public static function reportByManager($dateFrom, $dateTo)
    {
        $usages = Yii::$app->db->createCommand("
            SELECT us.*, u.`name`, u.id, c.id AS `client_id`
                FROM user_users u
                INNER JOIN client_contract cr ON cr.account_manager = u.`user`
                INNER JOIN clients c ON c.contract_id = cr.id
                INNER JOIN client_contragent cg ON cr.contragent_id = cg.id
                INNER JOIN (
                    SELECT t.`client`, 1 AS `count`, s.`date_start` AS `dateFrom`, 'departure' AS `type`
                        FROM tt_troubles t
                        INNER JOIN tt_stages s ON s.trouble_id = t.id
                        INNER JOIN tt_doers d ON d.stage_id = s.stage_id
                        INNER JOIN tt_doer_stages ds ON ds.id = d.doer_id
                        WHERE ds.status = 'done'
                    UNION ALL
                        SELECT `client`, `no_of_lines` AS `count`, `actual_from` AS `dateFrom`,
                        IF(`type_id` = 'line', 'line_free', IF(`type_id` = '7800', 'number_8800', `type_id`)) AS `type`
                        FROM usage_voip
                        WHERE `status` = 'working' AND `prev_usage_id` = 0 AND `type_id` != 'operator'
                    UNION ALL
                        SELECT `client`, `amount` AS `count`, `actual_from` AS `dateFrom`, 'vpbx' AS `type`
                        FROM usage_virtpbx
                        WHERE status = 'working' AND prev_usage_id = 0
                ) us ON us.`client` = c.`client`
                WHERE u.usergroup = 'account_managers' OR u.usergroup = 'manager'
        ")->queryAll(\PDO::FETCH_ASSOC);

        $managers = [];
        $clients = [];

        $dateFrom = strtotime($dateFrom);
        $dateTo = strtotime($dateTo);

        foreach ($usages as $usage) {
            if (!isset($managers[$usage['id']])) {
                $managers[$usage['id']]['name'] = $usage['name'];
                $managers[$usage['id']]['data'] = [
                    'number_new' => 0,
                    'number_old' => 0,
                    'line_new' => 0,
                    'line_old' => 0,
                    'line_free_new' => 0,
                    'line_free_old' => 0,
                    'number_8800_new' => 0,
                    'number_8800_old' => 0,
                    'vpbx_new' => 0,
                    'vpbx_old' => 0,
                    'departure' => 0,
                ];
            }

            $type = $usage['type'];

            if ($type == 'departure' && strtotime($usage['dateFrom']) <= $dateTo && strtotime($usage['dateFrom']) >= $dateFrom) {
                $managers[$usage['id']]['data'][$type]++;
            } else {
                if (isset($clients[$usage['client_id']][$type])) {
                    $suffix = '_old';
                } else {
                    $clients[$usage['client_id']][$type] = true;
                    $suffix = '_new';
                }
                if (strtotime($usage['dateFrom']) <= $dateTo && strtotime($usage['dateFrom']) >= $dateFrom) {
                    if ($type == 'number') {
                        $managers[$usage['id']]['data'][$type . $suffix]++;
                        $managers[$usage['id']]['data']['line' . $suffix] += $usage['count'];
                    } else {
                        $managers[$usage['id']]['data'][$type . $suffix]++;
                    }
                }
            }
        }

        return array_filter($managers, function ($manager) {
            return array_sum($manager['data']) > 0;
        });
    }

    public static function reportByPartner($dateFrom, $dateTo)
    {
        $usages = Yii::$app->db->createCommand("
            SELECT us.*, rcg.`name`, rcr.id, c.id AS `client_id`
                FROM client_contract_reward r
                INNER JOIN client_contract rcr ON rcr.id = r.contract_id
                INNER JOIN client_contragent rcg ON rcg.id = rcr.contragent_id

                INNER JOIN client_contragent cg ON cg.partner_contract_id = rcr.id
                INNER JOIN client_contract cr ON cg.id = cr.contragent_id
                INNER JOIN clients c ON c.contract_id = cr.id
                INNER JOIN (
                    SELECT t.`client`, 1 AS `count`, s.`date_start` AS `dateFrom`, 'departure' AS `type`
                        FROM tt_troubles t
                        INNER JOIN tt_stages s ON s.trouble_id = t.id
                        INNER JOIN tt_doers d ON d.stage_id = s.stage_id
                        INNER JOIN tt_doer_stages ds ON ds.id = d.doer_id
                        WHERE ds.status = 'done'
                    UNION ALL
                        SELECT `client`, `no_of_lines` AS `count`, `actual_from` AS `dateFrom`,
                        IF(`type_id` = 'line', 'line_free', IF(`type_id` = '7800', 'number_8800', `type_id`)) AS `type`
                        FROM usage_voip
                        WHERE `status` = 'working' AND `prev_usage_id` = 0 AND `type_id` != 'operator'
                    UNION ALL
                        SELECT `client`, `amount` AS `count`, `actual_from` AS `dateFrom`, 'vpbx' AS `type`
                        FROM usage_virtpbx
                        WHERE status = 'working' AND prev_usage_id = 0
                ) us ON us.`client` = c.`client`
        ")->queryAll(\PDO::FETCH_ASSOC);

        $partners = [];
        $clients = [];

        $dateFrom = strtotime($dateFrom);
        $dateTo = strtotime($dateTo);

        foreach ($usages as $usage) {
            if (!isset($partners[$usage['id']])) {
                $partners[$usage['id']]['name'] = $usage['name'];
                $partners[$usage['id']]['data'] = [
                    'number_new' => 0,
                    'number_old' => 0,
                    'line_new' => 0,
                    'line_old' => 0,
                    'line_free_new' => 0,
                    'line_free_old' => 0,
                    'number_8800_new' => 0,
                    'number_8800_old' => 0,
                    'vpbx_new' => 0,
                    'vpbx_old' => 0,
                    'departure' => 0,
                ];
            }

            $type = $usage['type'];

            if (isset($clients[$usage['client_id']][$type])) {
                $suffix = '_old';
            } else {
                $clients[$usage['client_id']][$type] = true;
                $suffix = '_new';
            }
            if (strtotime($usage['dateFrom']) <= $dateTo && strtotime($usage['dateFrom']) >= $dateFrom) {
                if ($type == 'number') {
                    $partners[$usage['id']]['data'][$type . $suffix]++;
                    $partners[$usage['id']]['data']['line' . $suffix] += $usage['count'];
                } else {
                    $partners[$usage['id']]['data'][$type . $suffix]++;
                }
            }

        }

        return array_filter($partners, function ($partner) {
            return array_sum($partner['data']) > 0;
        });
    }
}
