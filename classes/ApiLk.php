<?php 

class ApiLk
{
    public function getBalanceList($clientId)
    {
        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId))
            throw new Exception("Неверный номер лицевого счета!");
    
    
        $c = ClientCard::find_by_id($clientId);
        if(!$c)
        {
            throw new Exception("Лицевой счет не найден!");
        }
    
        $params = array("client_id" => $c->id, "client_currency" => $c->currency);
    
        list($R, $sum, ) = BalanceSimple::get($params);

        $cutOffDate = self::_getCutOffDate($clientId);
    
        $bills = array();
        foreach ($R as $r)
        {
            if (strtotime($r["bill"]["bill_date"]) < $cutOffDate)
                continue;
    
            $b = $r["bill"];
            $bill = array(
                            "bill_no"   => $b["bill_no"],
                            "bill_date" => $b["bill_date"],
                            "sum"       => $b["sum"],
                            "type"      => $b["nal"],
                            "pays"      => array()
            );
    
            foreach ($r["pays"] as $p)
            {
                if (strtotime($p["payment_date"]) < $cutOffDate)
                    continue;
    
                $bill["pays"][] = array(
                                "no"   => $p["payment_no"],
                                "date" => $p["payment_date"],
                                "type" => self::_getPaymentTypeName($p),
                                "sum"  => $p["sum_rub"]
                );
            }
            if ($b["is_lk_show"] == '1')
                $bills[] = $bill;
        }
    
        $sum = $sum["RUR"];
    
        $p = Payment::first(array(
                        "select" => "sum(sum_rub) as sum",
                        "conditions" => array("client_id" => $c->id)
        )
        );
    
        $nSum = array(
                        "payments" => $p ? $p->sum : 0.00,
                        "bills" => $sum["bill"],
                        "saldo" => $sum["delta"],
                        "saldo_date" => $sum["ts"] ? date("Y-m-d", $sum["ts"]) : ""
        );
    
        return array("bills" => $bills, "sums" => $nSum);
    }

    public function getUserBillOnSum($clientId, $sum)
    {
        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId))
            throw new Exception("Неверный номер лицевого счета!");
    
        $sum = (float)$sum;
    
        if(!$sum || $sum < 1 || $sum > 1000000)
            throw new Exception("Ошибка в данных");
    
    
        $c = ClientCard::find_by_id($clientId);
        if(!$c)
            throw new Exception("Лицевой счет не найден!");
    
        /* !!! проверка поличества созданных счетов */
    
    
        $bill = self::_getUserBillOnSum_fromDB($clientId, $sum);
    
        if(!$bill)
        {
            include_once INCLUDE_PATH.'bill.php';
    
            $newBill = new Bill(null, $clientId, strtotime(date("Y-m-d")), 0, 'RUR', true, true);
            $newBill->AddLine("RUR", Encoding::toKoi8r("Авансовый платеж"), 1, $sum/1.18, 'zadatok');
            $newBill->save();
    
            $bill = self::_getUserBillOnSum_fromDB($clientId, $sum);
        }
    
        if(!$bill)
            throw new Exception("Невозможно создать счет");
    
        return $bill;
    }

	public function getBillUrl($billNo)
	{
		$bill = NewBill::first(array("bill_no" => $billNo));
		if(!$bill)
			throw new Exception("Счет не найден");

		if(!defined('API__print_bill_url') || !API__print_bill_url)
			throw new Exception("Не установлена ссылка на печать документов");

		$R = array('bill'=>$billNo,'object'=>"bill-2-RUR",'client'=>$bill->client_id);
		return API__print_bill_url.udata_encode_arr($R);
	}

    public function getReceiptURL($clientId, $sum)
    {
        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId))
            throw new Exception("Неверный номер лицевого счета!");
    
        $sum = (float)$sum;
    
        if(!$sum || $sum < 1 || $sum > 1000000)
            throw new Exception("Ошибка в данных");
    
    
        $c = ClientCard::find_by_id($clientId);
        if(!$c)
            throw new Exception("Лицевой счет не найден!");
    
        $R = array("sum" => $sum, 'object'=>"receipt-2-RUR",'client'=>$c->id);
        return API__print_bill_url.udata_encode_arr($R);
    }

    public function getPropertyPaymentOnCard($clientId, $sum)
    {
        global $db;
    
        if(!defined("UNITELLER_SHOP_ID") || !defined("UNITELLER_PASSWORD"))
            throw new Exception("Не заданы параметры для UNITELLER в конфиге");
    
        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId))
            throw new Exception("Неверный номер лицевого счета!");
    
        $sum = (float)$sum;
    
        if(!$sum || $sum < 1 || $sum > 1000000)
            throw new Exception("Ошибка в данных");
    
    
        $c = ClientCard::find_by_id($clientId);
        if(!$c)
            throw new Exception("Лицевой счет не найден!");
    
    
        $sum = number_format($sum, 2, '.', '');
        $orderId = $db->QueryInsert('payments_orders', array(
                        'type' => 'card',
                        'client_id' => $clientId,
                        'sum' => $sum
        )
        );
    
        $signature = strtoupper(md5(UNITELLER_SHOP_ID . $orderId . $sum . UNITELLER_PASSWORD));
    
        return array("sum" => $sum, "order" => $orderId, "signature" => $signature);
    }

    public function updateUnitellerOrder($orderId)
    {
        return true;
        exit();
    }

    public function getBill($clientId, $billNo)
    {
        if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId))
            throw new Exception("Неверный номер лицевого счета!");
    
        $c = ClientCard::find_by_id($clientId);
        if(!$c)
            throw new Exception("Лицевой счет не найден!");
    
        $b = NewBill::first(array("conditions" => array("client_id" => $clientId, "bill_no" => $billNo, "is_lk_show" => "1")));
        if(!$b)
            throw new Exception("Счет не найден!");
    
        $lines = array();
    
        foreach($b->lines as $l)
        {
            $lines[] = array(
                            "item"      => Encoding::toUtf8($l->item),
                            "date_from" => $l->date_from->format("d-m-Y"),
                            "amount"    => $l->amount,
                            "price"     => number_format($l->price, 2, '.',''),
                            "sum"       => number_format($l->sum, 2, '.','')
            );
        }

        include_once INCLUDE_PATH.'bill.php';
        include_once PATH_TO_ROOT . "modules/newaccounts/module.php";
        $curr_bill = new Bill($billNo);
        $dt = $curr_bill->getBill2Doctypes();

        return array(
                        "bill" => array(
                                        "bill_no" => $b->bill_no,
                                        "is_rollback" => $b->is_rollback,
                                        "is_1c" => $b->is1C(),
                                        "lines" => $lines,
                                        "sum_total" => number_format($b->sum, 2, '.',''),
                                        "dtypes" => $dt
                        ),
                        "link" => array(
                                        "bill" => API__print_bill_url.udata_encode_arr(array('bill'=>$billNo,'object'=>"bill-2-RUR", "client" => $clientId)),
                                        "invoice1" => API__print_bill_url.udata_encode_arr(array('bill'=>$billNo,'object'=>"invoice-1", "client" => $clientId)),
                                        "invoice2" => API__print_bill_url.udata_encode_arr(array('bill'=>$billNo,'object'=>"invoice-2", "client" => $clientId)),
                                        "akt1" => API__print_bill_url.udata_encode_arr(array('bill'=>$billNo,'object'=>"akt-1", "client" => $clientId)),
                                        "akt2" => API__print_bill_url.udata_encode_arr(array('bill'=>$billNo,'object'=>"akt-2", "client" => $clientId)),
                        ),
        );
    }

    public function getDomainList($clientId)
    {
        $ret = array();

        foreach (NewBill::find_by_sql('
				SELECT
					`d`.`id`,
					CAST(`d`.`actual_from` AS DATE) AS `actual_from`,
					CAST(`d`.`actual_to` AS DATE) AS `actual_to`,
					`d`.`domain`,
					`d`.`paid_till`,
					IF ((`actual_from` <= NOW()) AND (`actual_to` > NOW()), 1, 0) AS `actual`
				FROM
					`domains` AS `d`
				INNER JOIN `clients` ON (`d`.`client` = `clients`.`client`)
				WHERE
					`clients`.`id`=?
				ORDER BY
					IF ((`actual_from` <= NOW()) AND (`actual_to` > NOW()), 0, 1) ASC,
					`actual_from` DESC
				', array($clientId)) as $d)
        {
            $ret[] = self::_exportModelRow(array("id", "actual_from", "actual_to", "domain", "paid_till", "actual"), $d);
        }
    
        return $ret;
    
    
    }

    public function getEmailList($clientId)
    {
    
        $ret = array();
    
        foreach(NewBill::find_by_sql('
				SELECT
					`e`.`id`,
					CAST(`e`.`actual_from` AS DATE) AS `actual_from`,
					CAST(`e`.`actual_to` AS DATE) AS `actual_to`,
					`e`.`local_part`,
					`e`.`domain`,
					`e`.`box_size`,
					`e`.`box_quota`,
					`e`.`status`,
                    `e`.`spam_act`,
					IF ((`actual_from` <= NOW()) AND (`actual_to` > NOW()), 1, 0) AS `actual`
				FROM `emails` AS `e`
				INNER JOIN `clients` ON (`e`.`client` = `clients`.`client`)
				WHERE
					`clients`.`id` = ?
				ORDER BY
					`local_part`,
					IF ((`actual_from` <= NOW()) AND (`actual_to` > NOW()), 0, 1) ASC,
					`actual_from` DESC
				', array($clientId)) as $e)
        {
    
            $line = self::_exportModelRow(array("id", "actual_from", "actual_to", "local_part", "domain", "box_size", "box_quota", "status", "actual","spam_act"), $e);
            $line['email'] = $line['local_part'].'@'.$line['domain'];
            $ret[] = $line;
    
        }
    
        return $ret;
    }

    public function getVoipList($clientId, $isSimple = false)
    {
        global $db_ats;
        $ret = array();
    
        $card = ClientCard::first(array("id" => $clientId));
    
        if (!$card)
            return $ret;

        $isDBAtsInited = $db_ats && $db != $db_ats;
    
        foreach(NewBill::find_by_sql(
                '
                    SELECT
                        u.id,
                        t.name AS tarif_name,
                        u.E164 AS number,
                        actual_from,
                        actual_to,
                        no_of_lines,
                        IF ((`u`.`actual_from` <= NOW()) AND (`u`.`actual_to` > NOW()), 1, 0) AS `actual`,
                        u.region
                    FROM (
                        SELECT
                            u.*,
                            (
                                SELECT
                                    id_tarif
                                FROM
                                    log_tarif
                                WHERE
                                    service = "usage_voip"
                                    AND date_activation<NOW()
                                    AND id_service= u.id
                                ORDER BY
                                    date_activation DESC,
                                    id DESC
                                LIMIT 1
                            ) AS tarif_id
                        FROM
                            usage_voip u, (
                                SELECT
                                    MAX(actual_from) AS max_actual_from,
                                    E164
                                FROM
                                    usage_voip
                                WHERE
                                    client=?
                                GROUP BY E164
                            ) a
                        WHERE
                                a.e164 = u.e164
                            AND client=?
                            AND max_actual_from = u.actual_from
                            AND if(actual_to < cast(NOW() as date),  actual_from > cast( now() - interval 2 month as date), true)
                        ) AS `u`
    
                    LEFT JOIN tarifs_voip t ON (
                        t.id = u.tarif_id
                    )
                     ORDER BY
                         `u`.`actual_from` DESC
    
                ', array($card->client, $card->client)) as $v)
        {
            $line =  self::_exportModelRow(array("id", "number", "no_of_lines", "actual_from", "actual_to", "actual","tarif_name", "region"), $v);

            if ($isDBAtsInited)
            {
                $line["vpbx"] = virtPbx::number_isOnVpbx($clientId, $line["number"]) ? 1 : 0;
            } else {
                $line["vpbx"] = 0;
            }
            $ret[] = $isSimple ? $line["number"] : $line;
        }
    
        return $ret;
    }

    public function getVpbxList($clientId)
    {
        $ret = array();
    
        foreach(NewBill::find_by_sql('
            SELECT 
                a.*, 
                `t`.description AS tarif_name,
                `t`.`price`,
                `t`.`space`,
                `t`.`num_ports`
                 FROM (
                    SELECT
                        `u`.`id`,
                        `u`.`amount`,
                        `u`.`actual_to`,
                        IF ((`u`.`actual_from` <= NOW()) AND (`u`.`actual_to` > NOW()), 1, 0) AS `actual`,
                        `u`.`status`,
                        `d`.`name` AS city,
                        (SELECT id_tarif FROM log_tarif WHERE service="usage_virtpbx" AND id_service=u.id AND date_activation<NOW() ORDER BY date_activation DESC, id DESC LIMIT 1) AS cur_tarif_id,
                        (SELECT date_activation FROM log_tarif WHERE service="usage_virtpbx" AND id_service=u.id AND date_activation<now() ORDER BY date_activation DESC, id DESC LIMIT 1) AS actual_from
                    FROM
                        `usage_virtpbx` AS `u`
                    INNER JOIN `clients` ON (
                        `u`.`client` = `clients`.`client`
                    )
                    LEFT JOIN `server_pbx` AS `s` ON (
                        `u`.`server_pbx_id` = `s`.`id`
                    )
                    LEFT JOIN `datacenter` AS `d` ON (
                        `s`.`datacenter_id` = `d`.`id`
                    )
                    WHERE
                        `clients`.`id`= ?
                    ORDER BY
                        `actual` DESC,
                        `actual_from` DESC
                )a
                LEFT JOIN `tarifs_virtpbx` AS `t` ON (`t`.`id` = cur_tarif_id)
            ', array($clientId)) as $v)
        {
            $line =  self::_exportModelRow(array("id", "amount", "status", "actual_from", "actual_to", "actual", "tarif_name", "price", "space", "num_ports","city"), $v);
            //$line['price'] = (double)round($line['price']*1.18);
            $ret[] = $line;
        }
    
        return $ret;
    }

    public static function getInternetList($clientId)
    {
        return self::_getConnectionsByType($clientId);
    }
    
    public static function getCollocationList($clientId)
    {
        return self::_getConnectionsByType($clientId, "C");
    }

    private static function _getConnectionsByType($clientId, $connectType = "I")
    {
        $ret = array();
    
        foreach(NewBill::find_by_sql('
				SELECT
					a.*,
					ti.name as tarif,
					ti.adsl_speed
				FROM (
					SELECT
						u.*,
						IF((u.actual_from<=NOW()) and (u.actual_to>NOW()),1,0) as actual,
						p.port_name as port,
						p.node,
						p.port_type,
						IF(u.actual_from<=(NOW()+INTERVAL 5 DAY),1,0) as actual5d,
						(SELECT
							t.id
						 FROM
							`log_tarif` AS `l`, `tarifs_internet` AS `t`
						 WHERE
							    `l`.`service` = "usage_ip_ports"
							AND `l`.`id_service` = u.id
							AND `t`.`id` = `l`.`id_tarif`
						 ORDER BY
							date_activation DESC,
							l.id DESC
						 LIMIT 1
						 ) AS tarif_id
					FROM usage_ip_ports u
					INNER JOIN clients c ON (c.client= u.client)
					LEFT JOIN tech_ports p ON (p.id=u.port_id)
					LEFT JOIN usage_ip_routes r ON (u.id=r.port_id)
					WHERE c.id = ?
					GROUP BY
						u.id
					order by
						actual desc,
						actual_from desc
					) a
				INNER JOIN tarifs_internet ti ON (ti.id = a.tarif_id)
				WHERE
					ti.type = ?
					', array($clientId, $connectType)) as $i)
        {
            $line = self::_exportModelRow(array("id", "address", "actual_from", "actual_to", "actual", "tarif", "port", "port_type", "status", "adsl_speed", "node"), $i);
    
            $line["nets"] = self::_getInternet_nets($line["id"]);
            $line["cpe"] = self::_getInternet_cpe($line["id"]);
    
            $ret[] = $line;
        }
    
        return $ret;
    }

    private static function _getInternet_nets($portId)
    {
        $ret = array();
    
        foreach(NewBill::find_by_sql('
				SELECT
					*,
					IF (`actual_from` <= NOW() and `actual_to` > NOW(), 1, 0) as `actual`
				FROM
					`usage_ip_routes`
				WHERE
					(port_id= ? )
				AND `actual_from` <= NOW()
				AND `actual_to` > NOW()
				ORDER BY
					`actual` DESC,
					`actual_from` DESC
				', array($portId)) as $net)
        {
            $ret[] = self::_exportModelRow(explode(",", "id,actual_from,actual_to,net,type,actual"), $net);
        }
        return $ret;
    }

    public static function _getInternet_cpe($portId)
    {
        foreach(NewBill::find_by_sql("
					SELECT
						`tech_cpe`.*,
						`type`,
						`vendor`,
						`model`,
						IF (`actual_from` <= NOW() AND `actual_to` >= NOW(), 1, 0) as `actual`
					FROM
						`tech_cpe`
					INNER JOIN `tech_cpe_models` ON `tech_cpe_models`.`id` = `tech_cpe`.`id_model`
					WHERE
							`tech_cpe`.`service` = 'usage_ip_ports'
						AND `tech_cpe`.`id_service` = ?
						AND (`actual_from` <= NOW() AND `actual_to` >= NOW())
					ORDER BY
						`actual` DESC,
						`actual_from` DESC
					", array($portId)) as $cpe)
        {
            $ret[] = self::_exportModelRow(array("actual_from", "actual_to","ip",  "type", "vendor", "model", "actual", "numbers"), $cpe);
        }
    
        return $ret;
    }

    public static function getExtraList($clientId)
    {
        $ret = array();
    
        foreach(NewBill::find_by_sql("
					SELECT
						`u`.`id`,
						`u`.`actual_from`,
						`u`.`actual_to`,
						`u`.`amount`,
						`t`.`description`,
						`t`.`period`,
						`t`.`price`,
						`t`.`param_name`,
						`u`.`param_value`,
						IF ((`actual_from` <= CAST(NOW() AS DATE)) AND (`actual_to` > CAST(NOW() AS DATE)), 1, 0) AS `actual`,
						IF ((`actual_from` <= (CAST(NOW() AS DATE) + INTERVAL 5 DAY)), 1, 0) AS `actual5d`
					FROM
						`usage_extra` AS `u`
					INNER JOIN `clients` ON (`clients`.`client` = `u`.`client`)
					LEFT JOIN `tarifs_extra` AS `t` ON t.id=u.tarif_id
					WHERE
						`clients`.`id` = ?
					AND `actual_from` <= CAST(NOW() AS DATE)
					AND `actual_to` > CAST(NOW() AS DATE)
					ORDER BY
						`actual` DESC,
						`actual_from` DESC
				", array($clientId)) as $service)
        {
            $line = self::_exportModelRow(array("id", "actual_from", "actual_to", "amount", "description", "period", "price", "param_name", "param_value", "actual", "actual5d"), $service);
    
            if ($line['param_name'])
            {
                $line['description'] = str_replace('%', '<i>' . $line['param_value'] . '</i>', $line['description']);
            }
            $line['amount'] = (double)$line['amount'];
            $line['price'] = (double)$line['price'];
    
            $ret[] = $line;
        }
    
        return $ret;
    }

    public static function getRegionList()
    {
        $line['voip_prefix'] = array();
        foreach(NewBill::find_by_sql("
            SELECT
                *
            FROM
                `regions`
            ORDER BY
                id>97 DESC, `name`
            ") as $service)
        {
            $line = self::_exportModelRow(array("id", "name", "short_name", "code"), $service);
            $line['voip_prefix'] = ClientCS::getVoipPrefix($line['id']);
            $ret[] = $line;
        }
        return $ret;
    }

    public static function getCollocationTarifs()
    {
        return self::_getInternetTarifs("C");
    }
    
    public static function getInternetTarifs()
    {
        return self::_getInternetTarifs("I");
    }

    public static function getDomainTarifs($currency = 'RUR', $status = 'public', $code = 'uspd')
    {
        $ret = array();
        foreach(NewBill::find_by_sql("
            SELECT
                id, description, period, price
            FROM
                `tarifs_extra`
            WHERE
                `currency` = ?
            AND `status` = ?
            AND `code` = ?
            AND `description` LIKE('".Encoding::toKOI8R('Хостинг\_')."%')
            ORDER BY
                `id`
            ", array($currency, $status, $code)) as $service)
        {
            $line = self::_exportModelRow(array("id", "description", "period", "price"), $service);
            $line['price'] = (double)round($line['price']);
            $line['description'] = str_replace('Хостинг_', '', $line['description']);
            $ret[] = $line;
        }
        return $ret;
    }

    public static function getVpbxTarifs($currency = 'RUR', $status = 'public')
    {
        $ret = array();
        foreach(NewBill::find_by_sql("
            SELECT
                *
            FROM
                `tarifs_virtpbx`
            WHERE
                `currency` = ?
            AND `status` = ?
            ORDER BY
                `id`
            ", array($currency, $status)) as $service)
        {
            $line = self::_exportModelRow(array("id", "description", "period", "price", "num_ports", "overrun_per_port", "space", "overrun_per_mb", "is_record", "is_fax"), $service);
            //$line['price'] = (double)round($line['price']*1.18);
            $ret[] = $line;
        }
        return $ret;
    }

    public static function getVoipTarifs($currency = 'RUR', $status = 'public', $dest = '4')
    {
        $fields = array('id','name','month_line','month_number','once_line','once_number','free_local_min','freemin_for_number','region');
        $ret = array();
        foreach(NewBill::find_by_sql("
            SELECT
                *
            FROM
                `tarifs_voip`
            WHERE
                `currency` = ?
            AND `status` = ?
            AND `dest` = ?
            ORDER BY
                `name`
            ", array($currency, $status, $dest)) as $service)
        {
            $line = self::_exportModelRow($fields, $service);
            $ret[] = $line;
        }
        return $ret;
    }

    public static function getFreeNumbers($region_id = 0, $isSimple = false)
    {
        $valid_regions = array('87', '88', '89', '94', '95', '93', '97', '98', '99');
        $ret = array();

        $q = "SELECT 
                a.number, a.region, a.price, a.beauty_level,
                IF(client_id IN ('9130', '764'), 'our', 
                    IF(date_reserved IS NOT NULL, 'reserv', 
                        IF(active_usage_id IS NOT NULL, 'used', 
                            IF(max_date >= (now() - INTERVAL 6 MONTH), 'stop', 'free' 
                            ) 
                        ) 
                    ) 
                ) AS status 
            FROM ( 
                SELECT 
                    number, region, price, client_id, beauty_level, 
                    (
                        SELECT 
                            MAX(actual_to) 
                        FROM 
                            usage_voip u 
                        WHERE 
                            u.e164 = v.number AND 
                            actual_from <= DATE_FORMAT(now(), '%Y-%m-%d')
                    ) AS max_date, 
                    (
                        SELECT 
                            MAX(id) 
                        FROM 
                            usage_voip u 
                        WHERE 
                            u.e164 = v.number AND 
                            (
                                (
                                    actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') AND 
                                    actual_to >= DATE_FORMAT(now(), '%Y-%m-%d')
                                ) OR 
                                actual_from >= '2029-01-01'
                            ) 
                    ) AS active_usage_id, 
                    (
                        SELECT 
                            MAX(created) 
                        FROM 
                            usage_voip u 
                        WHERE 
                            u.e164 = v.number AND 
                            actual_from = '2029-01-01'
                    ) AS date_reserved 
                FROM 
                    voip_numbers v 
                ".(($region_id > 0 && in_array($region_id, $valid_regions)) ? ' WHERE region=' . $region_id : '')."
                )a 
            LEFT JOIN clients c ON (c.id = a.client_id) 
            WHERE if(a.region = 99, if(number like '7495%', number like '74951059%' or beauty_level in (1,2),true), true)
            HAVING status IN ('free')";
        
        foreach(NewBill::find_by_sql($q/*"
          SELECT
                a.*, (
                    SELECT
                        max(actual_to)
                    FROM
                        usage_voip
                    WHERE
                        e164 = a.number
                        AND NOT (actual_from = '2029-01-01' AND actual_to='2029-01-01')
                    ) date_to
          FROM (
            SELECT
                number, beauty_level, price, voip_numbers.region
            FROM
                voip_numbers
            LEFT JOIN usage_voip uv ON (uv.E164 = voip_numbers.number)
            WHERE
                uv.E164 IS NULL
                AND client_id IS NULL
                AND (
                    (used_until_date IS NULL OR used_until_date < now() - interval 6 MONTH)
                    OR
                    (number LIKE '7495%' AND (used_until_date IS NULL OR used_until_date < now()))
                    OR
                        site_publish = 'Y'
                )
              )a
          HAVING date_to IS NULL OR date_to < now()
          ORDER BY if(beauty_level=0, 10, beauty_level) DESC, number
          "*/) as $service)
        {
            $line = self::_exportModelRow(array("number", "beauty_level", "price", "region"), $service);
            $line['full_number'] = $line['number'];
            $line['area_code'] = substr($line['number'],1,3);
            $l = strlen($line['number']);
            $number = $line["number"];
            $line['number'] = substr($line['number'],4,($l-8)).'-'.substr($line['number'],($l-4),2).'-'.substr($line['number'],($l-2),2);
            if ($line['price'] == '') $line['price_add'] = 'Договорная';
            else $line['price_add'] = 'руб.';
            $ret[] = $isSimple ? $number : $line;
        }
        return $ret;
    }

    public static function orderInternetTarif($client_id, $region_id, $tarif_id)
    {
        $order_str = 'Заказ услуги Интернет из Личного Кабинета. '.
                'Client ID: ' . $client_id . '; Region ID: ' . $region_id . '; Tarif ID: ' . $tarif_id;
    
        return array('status'=>'error','message'=>'Ошибка добавления заявки');
    }

    public static function orderCollocationTarif($client_id, $region_id, $tarif_id)
    {
        $order_str = 'Заказ услуги Collocation из Личного Кабинета. '.
                'Client ID: ' . $client_id . '; Region ID: ' . $region_id . '; Tarif ID: ' . $tarif_id;
    
        return array('status'=>'error','message'=>'Ошибка добавления заявки');
    }

    public static function orderVoip($client_id, $region_id, $number, $tarif_id, $lines_cnt)
    {
        global $db;
        //return array('status'=>'error','message'=>'Ошибка добавления заявки. Свяжитесь с менеджером.');

        $freeNumbers = self::getFreeNumbers(0, true);
        if (array_search($number, $freeNumbers) === false)
            return array('status'=>'error','message'=>'Номер не свободен!');
    
        $clientNumbers = self::getVoipList($client_id, true);
        if (array_search($number, $clientNumbers) !== false)
            return array('status'=>'error','message'=>'Номер уже используется!');
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $waiting_cnt = $db->GetValue("SELECT COUNT(*) FROM usage_voip WHERE client='".$client["client"]."' AND actual_from='2029-01-01' AND actual_to='2029-01-01'");
        if ($waiting_cnt >= 5) return array('status'=>'error','message'=>'Допускается резервировать не более 5 номеров!');
    
        $region = $db->GetRow("select name from regions where id='".$region_id."'");
    
        $lines_cnt = (int)$lines_cnt;
        if ($lines_cnt > 10) $lines_cnt = 10;
        if ($lines_cnt < 1) $lines_cnt = 1;

        $reserNumber = VoipReservNumber::reserv($number, $client_id, $lines_cnt, $tarif_id);
    
        $message = Encoding::toKOI8R("Заказ услуги IP Телефония из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: ".$client_id.")\n";
        $message .= Encoding::toKOI8R('Регион: ') . $region['name'] . "\n";
        $message .= Encoding::toKOI8R('Номер: ') . $number . "\n";
        $message .= Encoding::toKOI8R('Кол-во линий: ') . $lines_cnt . "\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $reserNumber["tarif"]["name"];
    
    
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager']), "usage_voip", $reserNumber["usage_id"]) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    
    }

    public static function orderVpbxTarif($client_id, $region_id, $tarif_id)
    {
        global $db;
    
        $client_id = (int)$client_id;
        $region_id = (int)$region_id;
        $tarif_id = (int)$tarif_id;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $region = $db->GetRow("select name from regions where id='".$region_id."'");
        $tarif = $db->GetRow("select id, description as name from tarifs_virtpbx where id='".$tarif_id."'");
    
        if (!$client || !$region || !$tarif)
            throw new Exception("Ошибка в данных!");
    
        $message = Encoding::toKOI8R("Заказ услуги Виртуальная АТС из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Регион: ') . $region['name'] . "\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif["name"];

        $vpbxId = $db->QueryInsert("usage_virtpbx", array(
                            "client"        => $client["client"],
                            "actual_from"   => "2029-01-01",
                            "actual_to"     => "2029-01-01",
                            "amount"        => 1,
                            "status"        => "connecting",
                            "server_pbx_id" => 1
                            )
                        );

        if (!$vpbxId) return array('status'=>'error','message'=>'Ошибка подключения услуги');

        $db->QueryInsert("log_tarif", array(
                            "service"         => 'usage_virtpbx',
                            "id_service"      => $vpbxId,
                            "id_tarif"        => $tarif_id,
                            "id_user"         => self::_getUserLK(),
                            "ts"              => array('NOW()'),
                            "date_activation" => date('Y-m-d')
                            )
                        );
    
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    }

    public static function orderDomainTarif($client_id, $region_id, $tarif_id)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $region = $db->GetRow("select name from regions where id='".$region_id."'");
        $tarif = $db->GetValue("select description from tarifs_extra where id='".$tarif_id."'");
    
        $message = Encoding::toKOI8R("Заказ услуги Домен из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Регион: ') . $region['name'] . "\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif;
    
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    
    }

    public static function orderEmail($client_id, $domain_id, $local_part, $password)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $domain = $db->GetValue("select domain from domains where id='".$domain_id."'");
        $email_id = $db->GetValue("
                select id from emails where 
                    client='".$client["client"]."' and 
                    domain='".$domain."' and 
                    local_part='".$local_part."'
                ");
        if ($email_id)
            return array('status'=>'error','message'=>'Такой Email уже используется');

        $emailId = $db->QueryInsert("emails", array(
                        "local_part"        => $local_part,
                        "domain"        => $domain,
                        "password"          => $password,
                        "client"   => $client["client"],
                        "box_size"   => "20",
                        "box_quota"     => "50000",
                        "status"        => "working",
                        "actual_from"   => array('NOW()'),
                        "actual_to"     => "2029-12-31"
                        )
                    );
        return array('status'=>'ok','message'=>'Почтовый ящик добавлен.');
    }

    public static function changeInternetTarif($client_id, $service_id, $tarif_id)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $address = $db->GetValue("select address from usage_ip_ports where id='".$service_id."'");
        $tarif = $db->GetValue("select name from tarifs_internet where id='".$tarif_id."'");
    
        $message = Encoding::toKOI8R("Заказ изменения тарифного плана услуги Интернет из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Адрес: ') . $address . "\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif;
    
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    
    }

    public static function changeCollocationTarif($client_id, $service_id, $tarif_id)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $address = $db->GetValue("select address from usage_ip_ports where id='".$service_id."'");
        $tarif = $db->GetValue("select name from tarifs_voip where id='".$tarif_id."'");
    
        $message = Encoding::toKOI8R("Заказ изменения тарифного плана услуги Collocation из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Адрес: ') . $address . "\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif;
    
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    
    }

    public static function changeVoipTarif($client_id, $service_id, $tarif_id)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $voip = $db->GetRow("select E164, status, actual_from from usage_voip where id='".$service_id."' AND client='".$client["client"]."'");
        $tarif = $db->GetValue("select name from tarifs_voip where id='".$tarif_id."'");
    
        $message = Encoding::toKOI8R("Заказ изменения тарифного плана услуги IP Телефония из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Номер: ') . $voip['E164'] . "\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif;
    
        if ($voip['actual_from'] == '2029-01-01') {
            $db->QueryUpdate("log_tarif", array("id_service", "service"), array("service" => "usage_voip", "id_service"=>$service_id, "id_tarif" => $tarif_id));
            $message .= Encoding::toKOI8R("\n\nтариф сменен, т.к. подключения не было");
        }
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    
    }

    public static function changeVpbxTarif($client_id, $service_id, $tarif_id)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $tarif = $db->GetValue("select description as name from tarifs_virtpbx where id='".$tarif_id."'");
    
        if (!$client || !$tarif)
            throw new Exception("Ошибка в данных!");
    
        $message = Encoding::toKOI8R("Заказ изменения тарифного плана услуги Виртуальная АТС из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Новый тарифный план: ') . $tarif;
    
        $vpbx = $db->GetRow($q = "select * from usage_virtpbx where id=".$service_id." and client = '".$client["client"]."'");
    
        if ($vpbx)
        {
            $first_day_next_month = date('Y-m-d', mktime(0, 0, 0, date("m")+1, 1, date("Y")));
            $db->QueryInsert("log_tarif", array(
                            "service"         => 'usage_virtpbx',
                            "id_service"      => $service_id,
                            "id_tarif"        => $tarif_id,
                            "id_user"         => self::_getUserLK(),
                            "ts"              => array('NOW()'),
                            "date_activation" => ($vpbx['actual_from'] == '2029-01-01') ? date('Y-m-d') : $first_day_next_month
                            )
                        );

            $message .= Encoding::toKOI8R("\n\nтариф изменен из личного кабинета");

            if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
                return array('status'=>'ok','message'=>'Заявка принята');
        }
    
    
        return array('status'=>'error','message'=>'Ошибка добавления заявки');
    }

    public static function changeDomainTarif($client_id, $service_id, $tarif_id)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $domain = $db->GetValue("select domain from domains where id='".$service_id."'");
        $tarif = $db->GetValue("select description from tarifs_extra where id='".$tarif_id."'");
    
        $message = Encoding::toKOI8R("Заказ на изменение услуги Домен из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Домен: ') . $domain . "\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif;
    
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    
    }

    public static function changeEmail($client_id, $email_id, $password)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $email = $db->GetRow("select * from emails where client='".$client['client']."' and id=".$email_id);
        if ($email) {
            $db->QueryUpdate(
                    "emails", 
                    array("id", "client"), 
                    array("id" => $email_id, "client"=>$client['client'], "password" => $password)
            );

            return array('status'=>'ok','message'=>'Пароль изменен');
        } else return array('status'=>'error','message'=>'Ошибка изменения пароля.');
    }

    public static function changeEmailSpamAct($client_id, $email_id, $spam_act)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $cur_spam_act = $db->GetValue("select spam_act from emails where client='".$client['client']."' and id=".$email_id);
        if ($cur_spam_act) {
            $db->QueryUpdate("emails", array("id", "client"), array("id" => $email_id, "client"=>$client['client'], "spam_act" => $spam_act));
        } else 
            return array('status'=>'error','message'=>'Ошибка изменения фильтрации спама');

        return array('status'=>'ok','message'=>'ok');
    }

    public static function getEmailAccess($client_id)
    {
        global $db;

        $res = array('add_email'=>0, 'domain_cnt'=>0);
        $clients = array(780,2339,2817,3680,3920,1378,447,1266,652,41,941,51,440,54,452,866,529);
        
        /*если клиент не в заданном списке - вернем пустой массив*/
        if (in_array($client_id, $clients))
            $res['add_email'] = 1;
        
        $res['domain_cnt'] = $db->GetValue("
                SELECT 
                    COUNT(1)
                FROM 
                    `domains` AS `d` 
                INNER JOIN `clients` ON (`d`.`client` = `clients`.`client`) 
                WHERE `clients`.`id`=".$client_id);
        
        return $res;
    }
    
    public static function disconnectInternet($client_id, $service_id)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $address = $db->GetValue("select address from usage_ip_ports where id='".$service_id."'");
    
        $message = Encoding::toKOI8R("Заказ на отключение услуги Интернет из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Адрес: ') . $address;
    
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    
    }

    public static function disconnectCollocation($client_id, $service_id)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $address = $db->GetValue("select address from usage_ip_ports where id='".$service_id."'");
    
        $message = Encoding::toKOI8R("Заказ на отключение услуги Collocation из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Адрес: ') . $address;
    
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    
    }

    public static function disconnectVoip($client_id, $service_id)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $voip = $db->GetRow("select E164, status, actual_from from usage_voip where id='".$service_id."' AND client='".$client["client"]."'");
    
        $message = Encoding::toKOI8R("Заказ на отключение услуги IP Телефония из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Номер: ') . $voip['E164'];
    
        if ($voip['actual_from'] == '2029-01-01') {
            $db->QueryDelete('log_tarif', array("service" => "usage_voip", 'id_service'=>$service_id));
            $db->QueryDelete('usage_voip', array('id'=>$service_id));
            $message .= Encoding::toKOI8R("\n\номер удален, т.к. подключения не было");
        }
    
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    }

    public static function disconnectVpbx($client_id, $service_id)
    {
        global $db;

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");

        $message = Encoding::toKOI8R("Заказ на отключение услуги Виртуальная АТС из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Услуга: ') . $service_id . " (Id: $service_id)\n";

        $vpbx = $db->GetRow($q = "select id, actual_from from usage_virtpbx where id=".$service_id." and client = '".$client["client"]."'");

        if ($vpbx)
        {
            if ($vpbx["actual_from"] == "2029-01-01")
            {
                $db->QueryDelete("log_tarif", array("id_service" => $vpbx["id"]));
                $db->QueryDelete("usage_virtpbx", array("id" => $vpbx["id"]));
    
                $message .= Encoding::toKOI8R("\n\nВиртуальная АТС отключена автоматически, т.к. подключения не было");
            }
    
            if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
                return array('status'=>'ok','message'=>'Заявка принята');
        }

        return array('status'=>'error','message'=>'Ошибка добавления заявки');
    }

    public static function disconnectDomain($client_id, $service_id)
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $domain = $db->GetRow("select domain from domains where id='".$service_id."'");
    
        $message = Encoding::toKOI8R("Заказ на отключение услуги Домен из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Домен: ') . $domain;
    
        if (self::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0)
            return array('status'=>'ok','message'=>'Заявка принята');
        else
            return array('status'=>'error','message'=>'Ошибка добавления заявки');
    
    }

    public static function disconnectEmail($client_id, $email_id, $action = 'disable')
    {
        global $db;
    
        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $email = $db->GetRow("select * from emails where client='".$client['client']."' and id=".$email_id);
        if ($email) {
            $db->QueryUpdate(
                    "emails", 
                    array("id", "client"), 
                    array(
                        "id" => $email_id, 
                        "client"=>$client['client'], 
                        "enabled" => (($action == 'disable') ? '0' : '1'),
                        "actual_to" => (($action == 'disable') ? array('NOW()') : '2029-12-31')
                    )
            );
            return array('status'=>'ok','message'=>(($action == 'disable') ? 'Почтовый ящик отключен.' : 'Почтовый ящик подключен.'));
        } else return array('status'=>'error','message'=>(($action == 'disable') ? 'Ошибка отключения ящика.' : 'Ошибка подключения ящика.'));
    }

    /**
     * Получение карточки клиента
     *
     */
    public static function getClientData($client_id = '')
    {
        if (is_array($client_id) || !$client_id || !preg_match("/^\d{1,6}$/", $client_id))
            throw new Exception("Неверный номер лицевого счета!");

        $ret = self::_exportModelRow(array('id','client','status','inn','kpp','address_jur','address_post','corr_acc',
                        'pay_acc','bik','address_post_real','signer_name','signer_position','address_connect','phone_connect',
                        'mail_who', 'company','company_full'), ClientCard::find_by_id($client_id));

        return $ret;
    }

    /**
     * Сохранение карточки клиента
     *
     */
    public static function saveClientData($client_id = '', $data = array())
    {
        global $db;
        $status_arr = array('income','connecting','testing');
        $edit_fields = array(
                        'inn','kpp','company_full','address_jur','address_post','pay_acc','bik',
                        'signer_name','signer_position','mail_who','address_connect','phone_connect'
        );
    
        if (is_array($client_id) || !$client_id || !preg_match("/^\d{1,6}$/", $client_id))
            throw new Exception("Неверный номер лицевого счета!");
    
        $client = $db->GetRow("select * from clients where '".addslashes($client_id)."' in (id, client)");
        if (!$client)
            throw new Exception("Неверный номер лицевого счета!");
    
        if (!in_array($client['status'], $status_arr))
            throw new Exception("Запрет редактирования клиента!");
    
        $edit_data = array('id'=>$client_id);
        foreach ($edit_fields as $fld) {
            if (isset($data[$fld])) {
                $v = htmlentities(trim(strip_tags(preg_replace(array('/\\\\+/','/\/\/+/'), array('\\','/'), $data[$fld]))),ENT_QUOTES);
                $edit_data[$fld] = substr($v, 0, 250);
            }
        }
    
        $res = $db->QueryUpdate('clients','id', self::_importModelRow($edit_data));
    
        return $res;
    }

    /**
     * Получение названия компании
     *
     */
    public static function getCompanyName($client_id = '')
    {
        if (is_array($client_id) || !$client_id || !preg_match("/^\d{1,6}$/", $client_id))
            throw new Exception("Неверный номер лицевого счета!");
    
        $ret = array();
        $client = ClientCard::find_by_id($client_id);
        if ($client) $ret = self::_exportModelRow(array('name'), $client->super);
    
        return $ret;
    }

    public static function getStatisticsVoipPhones($client = '')
    {
        global $db;
    
        $client = $db->GetRow("select * from clients where '".addslashes($client)."' in (id, client)");
    
        $usages = $db->AllRecords($q = "select u.id, u.E164 as phone_num, u.region, r.name as region_name from usage_voip u
                                       left join regions r on r.id=u.region
                                       where u.client='".addslashes($client['client'])."'
                                       order by u.region desc, u.id asc");
    
        $regions = array();
        foreach ($usages as $u)
        if (!isset($regions[$u['region']]))
            $regions[$u['region']] = $u['region'];
    
        $regions_cnt = count($regions);
    
        $phone = $last_region = '';
        $regions = $phones = array();
        if ($regions_cnt > 1) {
            $region = 'all';
            $phones['all'] = 'Все регионы';
        }
    
        if ($phone == '' && count($usages) > 0) {
            $phone = $usages[0]['region'];
        }
        if ($region != 'all') {
            $region = explode('_', $phone);
            $region = $region[0];
        }
    
        foreach ($usages as $r) {
            if ($region == 'all') {
                if (!isset($regions[$r['region']])) $regions[$r['region']] = array();
                if (!isset($regions[$r['region']][$r['id']])) $regions[$r['region']][$r['id']] = $r['id'];
            }
            if (substr($r['phone_num'],0,4)=='7095') $r['phone_num']='7495'.substr($r['phone_num'],4);
            if ($last_region != $r['region']){
                $phones[$r['region']] = Encoding::toUTF8($r['region_name']).' (все номера)';
                $last_region = $r['region'];
            }
            $phones[$r['region'].'_'.$r['phone_num']]='&nbsp;&nbsp;'.$r['phone_num'];
        }
        $ret = array();
        foreach ($phones as $k=>$v) $ret[] = array('id'=>$k, 'number'=>$v);
        return $ret;
    }

    public static function getStatisticsVoipData($client_id = '', $phone = 'all', $from = '', $to = '', $detality = 'day', $destination = 'all', $direction = 'both', $onlypay = 0)
    {
        global $db;
        include PATH_TO_ROOT . "modules/stats/module.php";
        $module_stats = new m_stats();
    
        $destination = (!in_array($destination,array('all','0','0-m','0-f','1','1-m','1-f','2','3'))) ? 'all': $destination;
        $direction = (!in_array($direction,array('both','in','out'))) ? 'both' : $direction;
    
        $client = $db->GetRow("select * from clients where '".addslashes($client_id)."' in (id, client)");
    
        $usages = $db->AllRecords($q = "select u.id, u.E164 as phone_num, u.region, r.name as region_name from usage_voip u
                                       left join regions r on r.id=u.region
                                       where u.client='".addslashes($client['client'])."'
                                       order by u.region desc, u.id asc");
    
        $regions = $phones_sel = array();
    
        $last_region = $region = '';
        if ($phone != 'all') {
            $region = explode('_', $phone);
            $region = $region[0];
        } else $region = 'all';
    
        foreach ($usages as $r) {
            if ($phone == 'all') {
                if (!isset($regions[$r['region']])) $regions[$r['region']] = array();
                if (!isset($regions[$r['region']][$r['id']])) $regions[$r['region']][$r['id']] = $r['id'];
            }
            if ($phone==$r['region'] || $phone==$r['region'].'_'.$r['phone_num']) $phones_sel[]=$r['id'];
        }
    
        $stats = array();
        if ($phone == 'all') {
    
            foreach ($regions as $region=>$phones_sel) {
                $stats[$region] = $module_stats->GetStatsVoIP($region,strtotime($from),strtotime($to),$detality,$client_id,$phones_sel,$onlypay,0,$destination,$direction, array());
            }
    
            $ar = array();
            $all_regions = $db->AllRecords('select id, name from regions');
            foreach ($all_regions as $reg) $ar[$reg['id']] =  Encoding::toUTF8($reg['name']);
            $stats = $module_stats->prepareStatArray($stats, $detality, $ar);
        } else {
            $stats = $module_stats->GetStatsVoIP($phone,strtotime($from),strtotime($to),$detality,$client_id,$phones_sel,$onlypay,0,$destination,$direction, array());
        }
        foreach ($stats as $k=>$r) {
            $stats[$k]["ts1"] = Encoding::toUTF8($stats[$k]["ts1"]);
            $stats[$k]["tsf1"] = Encoding::toUTF8($stats[$k]["tsf1"]);
            $stats[$k]["price"] = Encoding::toUTF8($stats[$k]["price"]);
            $stats[$k]["geo"] = Encoding::toUTF8($stats[$k]["geo"]);
        }
        return $stats;
    }

    public static function getStatisticsInternetRoutes($client_id = '')
    {
        global $db;
        include PATH_TO_ROOT . "modules/stats/module.php";
        $module_stats = new m_stats();
    
        $client = $db->GetRow("select * from clients where '".addslashes($client_id)."' in (id, client)");
    
        list($routes_all,$routes_allB)=$module_stats->get_routes_list($client['client']);
    
        return $routes_all;
    }

    public static function getStatisticsInternetData($client_id = '', $from = '', $to = '', $detality = 'day', $route = '', $is_coll = 0)
    {
        global $db;
        include PATH_TO_ROOT . "modules/stats/module.php";
        $module_stats = new m_stats();
    
        $client = $db->GetRow("select * from clients where '".addslashes($client_id)."' in (id, client)");
    
        list($routes_all,$routes_allB)=$module_stats->get_routes_list($client['client']);
    
        $from = strtotime($from);
        $to = strtotime($to);
    
        //если сеть не задана, выводим все подсети клиента.
        if($route){
            if(isset($routes_all[$route])){
                $routes=array($routes_all[$route]);
            }else{
                return array();
            }
        }else{
            $routes=array();
            foreach($routes_allB as $r)
                $routes[] = $r;
        }
    
        $stats = $module_stats->GetStatsInternet($client['client'],$from,$to,$detality,$routes,$is_coll);
        foreach ($stats as $k=>$r) {
            $stats[$k]["tsf"] = Encoding::toUTF8($stats[$k]["tsf"]);
            $stats[$k]["ts"] = Encoding::toUTF8($stats[$k]["ts"]);
        }
        return $stats;
    }

    public static function getStatisticsCollocationData($client_id = '', $from = '', $to = '', $detality = 'day', $route = '')
    {
        return self::getStatisticsInternetData($client_id, $from, $to, $detality, $route, 1);
    }

    public static function getServiceOptions($service, $clientId)
    {
        $o = new LkServiceOptions($service, $clientId);
        return $o->getOptions();
    }

    /**
     * Получение настроек уведомлений клиента
     *
     *@param int $client_id id клиента
     */
    public static function getAccountsNotification($client_id = '')
    {
        if (!self::validateClient($client_id))
            throw new Exception("Неверный номер лицевого счета!");
    
        $ret = array();
        foreach(ClientContact::find_by_sql("
                select c.id, c.type, c.data as info, n.min_balance, n.day_limit, n.add_pay_notif, n.status
                from client_contacts c
                left join lk_notice_settings n on n.client_contact_id=c.id
                left join user_users u on u.id=c.user_id
                where c.client_id='".$client_id."'
                and u.user='LK'
                ") as $v) {
                    $ret[] = self::_exportModelRow(array('id','type','info','min_balance','day_limit', 'add_pay_notif', 'status'), $v);
        }
        return $ret;
    }

    /**
     * Добавление контакта для уведомлений
     *
     *@param int $client_id id клиента
     *@param string $type тип (телефон или Email)
     *@param string $data значение
     */
    public static function addAccountNotification($client_id = '', $type = '', $data = '')
    {
        global $db;
        if (!self::validateClient($client_id))
            return array('status'=>'error','message'=>'Неверный номер лицевого счета!');

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        if (!$client)
            return array('status'=>'error','message'=>'Лицевой счет не найден!');

        $lk_user = $db->GetRow("select id, user from user_users where user='LK'");
        if (!$lk_user)
            return array('status'=>'error','message'=>'Ошибка добавления Контакта. Свяжитесь с менеджером.');
    
        $contact_cnt = $db->GetValue("SELECT COUNT(*) FROM client_contacts WHERE client_id='".$client_id."' AND user_id='".$lk_user["id"]."'");
        if ($contact_cnt >= 5) 
            return array('status'=>'error','message'=>'Допускается добавлять не более 5 контактов!');

        if (!in_array($type, array('email', 'phone')))
            return array('status'=>'error','message'=>'Неверный тип контакта.');
        
        if (!self::validateData($type, $data))
            return array('status'=>'error','message'=>'Неверный формат данных.');
    
        $contact_id = $db->GetValue("SELECT id FROM client_contacts WHERE client_id='".$client_id."' AND type='".$type."' AND data='".$data."'");
        if (!$contact_id) {
            $contact_id = $db->QueryInsert("client_contacts", array(
                        "client_id"     => $client_id,
                        "type"        => $type,
                        "data"          => $data,
                        "user_id"   => $lk_user['id'],
                        "comment"   => "",
                        "is_active"     => "1",
                        "is_official"        => "0"
                        )
                    );
        }

        if ($contact_id && $contact_id > 0) {
            $res = $db->QueryInsert("lk_notice_settings", array(
                            "client_contact_id" => $contact_id,
                            "client_id"         => $client_id
                            )
                        );
        } else 
            return array('status'=>'error','message'=>'Ошибка добавления контакта.');

        self::sendApproveMessage($client_id, $type, $data, $contact_id);

        return array('status'=>'ok','message'=>'Контакт добавлен');
    }

    public static function validateData($t = '', $d = '')
    {
        switch ($t) {
            case 'email':
                if (!preg_match("/^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/", $d)) 
                    return false;
            break;
            case 'phone':
                if (!preg_match("/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/", $d))
                    return false;
            break;
        }
        return true;
    }

    /**
     * Редактирование контакта для уведомлений
     *
     *@param int $client_id id клиента
     *@param int $contact_id id контакта
     *@param string $type тип (телефон или Email)
     *@param string $data значение
     */
    public static function editAccountNotification($client_id = '', $contact_id = '', $type = '', $data = '')
    {
        global $db;
        if (!self::validateClient($client_id))
            return array('status'=>'error','message'=>'Неверный номер лицевого счета!');

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");

        if (!self::validateContact($client_id, $contact_id))
            return array('status'=>'error','message'=>'Неверный идентификатор контакта!');

        if (!in_array($type, array('email', 'phone')))
            return array('status'=>'error','message'=>'Неверный тип контакта.');

        if (!self::validateData($type, $data))
            return array('status'=>'error','message'=>'Неверный формат данных.');

        $status = $db->GetValue("select status from lk_notice_settings where client_id='".$client_id."' and client_contact_id='".$contact_id."'");

        $res = $db->QueryUpdate("client_contacts", array('client_id','id'), array(
                        'type'=>$type,
                        'data'=>$data,
                        'client_id'=>$client_id,
                        'id'=>$contact_id
                        )
                    );
        if ($res) {
            $res = $db->QueryUpdate(
                    "lk_notice_settings",
                    array('client_id','client_contact_id'),
                    array(
                        'status'=>'connecting',
                        'client_id'=>$client_id,
                        'client_contact_id'=>$contact_id
                        )
                    );
        }

        if ($res && $status == 'working') {
            self::sendApproveMessage($client_id, $type, $data, $contact_id);
        }

        return array('status'=>'ok','message'=>'Контакт изменен');
    }

    /**
     * Удаление контакта для уведомлений
     *
     *@param int $client_id id клиента
     *@param int $contact_id id контакта
     */
    public static function disableAccountNotification($client_id = '', $contact_id = '')
    {
        global $db;
        if (!self::validateClient($client_id))
            return array('status'=>'error','message'=>'Неверный номер лицевого счета!');
        if (!self::validateContact($client_id, $contact_id))
            return array('status'=>'error','message'=>'Неверный идентификатор контакта!');
    
        $db->QueryDelete('lk_notice_settings', array('client_contact_id'=>$contact_id));
        $db->QueryDelete('client_contacts', array('id'=>$contact_id, 'client_id'=>$client_id));
    
        return array('status'=>'ok','message'=>'Контакт удален.');
    }

    /**
     * Активация контакта для уведомлений
     *
     *@param int $client_id id клиента
     *@param int $contact_id id контакта
     *@param string $code код активации
     */
    public static function activateAccountNotification($client_id = '', $contact_id = '', $code = '')
    {
        global $db;
        if (!self::validateClient($client_id))
            return array('status'=>'error','message'=>'Неверный номер лицевого счета!');
        if (!self::validateContact($client_id, $contact_id))
            return array('status'=>'error','message'=>'Неверный идентификатор контакта!');
        if ($code == '')
            return array('status'=>'error','message'=>'Код активации не задан!');
        
        $etalon_code = $db->GetValue("select activate_code from lk_notice_settings where client_id='".$client_id."' AND client_contact_id='".$contact_id."'");
        if ($etalon_code != $code)
            return array('status'=>'error','message'=>'Неверный код активации!');
        
        $res = $db->Query('update lk_notice_settings set status="working" where client_id="'.$client_id.'" and client_contact_id="'.$contact_id.'"');
        if ($res)
            return array('status'=>'ok','message'=>'Контакт активирован.');
        else 
            return array('status'=>'error','message'=>'Ошибка активации! Свяжитесь с менеджером.');
    }

    /**
     * Активация Email контакта для уведомлений
     *
     *@param int $client_id id клиента
     *@param int $contact_id id контакта
     *@param string $key ключ
     */
    public static function activatebyemailAccountNotification($client_id = '', $contact_id = '', $key = '')
    {
        global $db;
        if (!self::validateClient($client_id))
            return array('status'=>'error','message'=>'Неверный номер лицевого счета!');
        if (!self::validateContact($client_id, $contact_id))
            return array('status'=>'error','message'=>'Неверный идентификатор контакта!');

        if ($key == '' || $key != md5($client_id.'SeCrEt-KeY'.$contact_id))
            return array('status'=>'error','message'=>'Неверный код активации!');

        $res = $db->Query('update lk_notice_settings set status="working" where client_id="'.$client_id.'" and client_contact_id="'.$contact_id.'"');
        if ($res)
            return array('status'=>'ok','message'=>'Контакт активирован.');
        else
            return array('status'=>'error','message'=>'Ошибка активации! Свяжитесь с менеджером.');
    }

    /**
     * Сохранение настроек
     *
     *@param int $client_id id клиента
     *@param array $data данные
     */
    public static function saveAccountNotification($client_id = '', $data = array(), $min_balance = '0', $day_limit = '0')
    {
        global $db;
        if (!self::validateClient($client_id))
            return array('status'=>'error','message'=>'Неверный номер лицевого счета!');
    
        $res = array();
        foreach ($data as $name) 
        {
            $tmp = explode('__', $name);

            if(!isset($res[$tmp[1]])) 
                $res[$tmp[1]] = array(
                        'client_contact_id'=>$tmp[1],
                        'min_balance'=>0, 
                        'day_limit'=>0, 
                        'add_pay_notif'=>0);

            $res[$tmp[1]][$tmp[0]] = 1;
        }

        $allSavedContacts = $db->AllRecords("
                SELECT id 
                FROM `client_contacts` 
                WHERE `client_id` = '".mysql_escape_string($client_id)."' AND `user_id` = (select id from user_users where user = 'LK') AND `is_active` = '1' ", "id");

        foreach ($res as $contact_id=>$d) 
        {
            if (!isset($allSavedContacts[$contact_id]))
                continue;

            unset($allSavedContacts[$contact_id]);

            $cc_id = $db->GetValue("select client_contact_id 
                    from lk_notice_settings 
                    where client_contact_id='".$d['client_contact_id']."' and client_id='".$client_id."'");

            $data = array(
                    'client_contact_id'=>$d['client_contact_id'],
                    'client_id'=>$client_id,
                    'min_balance'=>$d['min_balance'],
                    'day_limit'=>$d['day_limit'],
                    'add_pay_notif'=>$d['add_pay_notif']
                    );
            if ($cc_id) {
                $db->QueryUpdate('lk_notice_settings',array('client_contact_id','client_id'),$data);
            } else {
                $db->QueryInsert('lk_notice_settings',$data);
            }
        }

        if ($allSavedContacts) //for deletion because there is no data
        {
            foreach($db->AllRecords("select client_contact_id as id from lk_notice_settings where client_contact_id in ('".implode("','", array_keys($allSavedContacts))."')", "id") as $contact_id => $data)
            {
                $db->QueryUpdate("lk_notice_settings", "client_contact_id", array(
                            "client_contact_id" => $contact_id,
                            "min_balance" => 0,
                            "day_limit" => 0,
                            "add_pay_notif" => 0));
            }
        }

        $clientSettings = $db->GetValue("select * from lk_client_settings where client_id='".$client_id."'");

        $data = array(
                'client_id'=>$client_id,
                'min_balance'=>$min_balance,
                'day_limit'=>$day_limit
                );
        if ($clientSettings) 
        {
            if ($clientSettings["is_min_balance_sent"] && $clientSettings["min_balance"] < $data["min_balance"])
            {
                $data["is_min_balance_sent"] = 0;
            }

            if ($clientSettings["is_day_limit_sent"] && $clientSettings["day_limit"] < $data["day_limit"])
            {
                $data["is_day_limit_sent"] = 0;
            }

            $db->QueryUpdate('lk_client_settings',array('client_id'),$data);
        } else {
            $db->QueryInsert('lk_client_settings',$data);
        }
        
        return array('status'=>'ok','message'=>'Данные сохранены.');
    }

    /**
     * Получение настроек клиента
     *
     *@param int $client_id id клиента
     */
    public static function getAccountSettings($client_id = '')
    {
        if (!self::validateClient($client_id))
            throw new Exception("Неверный номер лицевого счета!");
    
        $ret = array();
        foreach(ClientContact::find_by_sql("
                select *
                from lk_client_settings
                where client_id='".$client_id."'
                ") as $v) {
                    $ret = self::_exportModelRow(array('client_id','min_balance','day_limit'), $v);
        }
        return $ret;
    }

    public static function sendApproveMessage($client_id, $type, $data, $contact_id)
    {
        global $design, $db;

        $res = false;
        if ($type == 'email') {
            $key = md5($client_id.'SeCrEt-KeY'.$contact_id);
            $db->QueryUpdate(
                    'lk_notice_settings',
                    array('client_contact_id','client_id'),
                    array('client_contact_id'=>$contact_id,'client_id'=>$client_id,'activate_code'=>$key)
                    );

            $url = 'https://'.CORE_SERVER.'/lk/accounts_notification/activate_by_email?client_id=' . $client_id . '&contact_id=' . $contact_id . '&key=' . $key;
            $design->assign(array('url'=>$url));
            $message = $design->fetch('letters/notification/approve.tpl');
            $params = array(
                            'data'=>$data,
                            'subject'=>Encoding::toKoi8r('Подтверждение Email адреса для уведомлений'),
                            'message'=>Encoding::toKoi8r($message),
                            'type'=>'email',
                            'contact_id'=>$contact_id
                        );
            $id = $db->QueryInsert('lk_notice', $params);
            if ($id) $res = true;
        } else if ($type == 'phone') {
            $code = '';
            for ($i=0;$i<6;$i++) $code .= rand(0,9);
            $db->QueryUpdate(
                    'lk_notice_settings',
                    array('client_contact_id','client_id'),
                    array('client_contact_id'=>$contact_id,'client_id'=>$client_id,'activate_code'=>$code)
                    );
            $params = array(
                            'data'=>$data,
                            'message'=>Encoding::toKoi8r('Код активации: ' . $code),
                            'type'=>'phone',
                            'contact_id'=>$contact_id
                        );
            $id = $db->QueryInsert('lk_notice', $params);
            if ($id) $res = true;
        }
        return $res;
    }


    public static function validateClient($id) 
    {
        if (is_array($id) || !$id || !preg_match("/^\d{1,6}$/", $id))
            return false;

        $c = ClientCard::find_by_id($id);
        if(!$c)
            return false;

        return true;
    }

    public static function validateContact($clientId, $id) 
    {
        global $db;

        if (is_array($id) || !$id || !preg_match("/^\d{1,6}$/", $id))
            return false;

        $contactId = $db->GetValue("SELECT id FROM client_contacts WHERE client_id='".mysql_escape_string($clientId)."' AND id = '".mysql_escape_string($id)."'");
        if (!$contactId)
            return false;

        return true;
    }


    private function _getPaymentTypeName($pay)
    {
        switch ($pay["type"])
        {
        	case 'bank': $v = "Банк"; break;
        	case 'prov': $v = "Наличные"; break;
        	case 'neprov': $v = "Эл.деньги"; break;
            case 'ecash': $v = "Эл.деньги";
                switch($pay["ecash_operator"])
                {
                    case 'yandex': $v = "Яндекс.Деньги"; break;
                    case 'cyberplat': $v = "Cyberplat"; break;
                    case 'uniteller': $v = "Uniteller"; break;
                }
                break;
        	default: $v = "Банк";
        }
    
        return $v;
    }

    private function _getCutOffDate($clientId)
    {
        global $db;
    
        $dateStart = $db->GetValue(
                "
                SELECT
                    UNIX_TIMESTAMP(if(apply_ts = '0000-00-00', cast(l.ts as date), apply_ts)) as ts
                FROM
                    `clients` c,
                    `log_client` l,
                    log_client_fields f
                WHERE
                        c.id = l.client_id
                    AND f.ver_id = l.id
                    AND ts >= '2012-04-01 00:00:00'
                    AND field = 'inn'
                    AND value_from != ''
                    AND c.id = '".$clientId."'
                ORDER BY ts DESC
                LIMIT 1");
    
        if(!$dateStart)
            $dateStart = strtotime("2012-04-01");
    
        return $dateStart;
    }

    private function _getUserBillOnSum_fromDB($clientId, $sum)
    {
        global $db;
    
        return $db->GetValue(
                "SELECT
				b.bill_no
			FROM
				`newbills` b, newbill_lines l
			where
					b.bill_no = l.bill_no
				and client_id = '".$clientId."'
				and is_user_prepay = 1
				and l.sum = '".$sum."'");
    }

    private static function _exportModelRow($fields, &$row)
    {
        $spec_chars = array('/\t/', '/\f/','/\n/','/\r/','/\v/');
        $line = array();
        foreach ($fields as $field)
        {
            $line[$field] = Encoding::toUtf8(preg_replace($spec_chars,' ',$row->{$field}));
        }
        return $line;
    }

    private static function _importModelRow($fields)
    {
        foreach ($fields as $k=>$v)
        {
            $fields[$k] = Encoding::toKOI8R($v);
        }
        return $fields;
    }

    public static function _getInternetTarifs($type = 'I', $currency = 'RUR', $status = 'public')
    {
        $ret = array();
        foreach(NewBill::find_by_sql("
            SELECT
                *
            FROM
                `tarifs_internet`
            WHERE
                `type` = ?
            AND `currency` = ?
            AND `status` = ?
            ORDER BY
                `id`
            ", array($type, $currency, $status)) as $service)
        {
            $line = self::_exportModelRow(array("id", "name", "pay_once", "pay_month", "mb_month", "pay_mb", "comment", "type_internet", "sum_deposit", "type_count", "month_r", "month_r2", "month_f", "pay_r", "pay_r2", "pay_f", "adsl_speed"), $service);
            $ret[] = $line;
        }
        return $ret;
    }

    public static function createTT($message = '', $client = '', $user = '', $service = '', $service_id = 0)
    {
        include_once PATH_TO_ROOT . "modules/tt/module.php";
        $tt = new m_tt();
    
        $R = array(
                        'trouble_type' => 'task',
                        'trouble_subtype' => 'task',
                        'client' => $client,
                        'time' => '',
                        'date_start' => date('Y-m-d H:i:s'),
                        'date_finish_desired' => date('Y-m-d H:i:s'),
                        'problem' => $message,
                        'is_important' => '0' ,
                        'bill_no' => null ,
                        'service' => $service,
                        'service_id' => $service_id,
                        'user_author' => "AutoLK"
        );

        if ($user == "ava") {
            mail("ava@mcn.ru", "[lk] Заказ услуги", $message);
        }
    
        return $tt->createTrouble($R, $user);
    }

    private function _getUserForTrounble($manager)
    {
        $default_manager = "ava";
    
        if (defined("API__USER_FOR_TROUBLE")) return API__USER_FOR_TROUBLE;
        else if (strlen($manager)) return $manager;
        else return $default_manager;
    }

    private function _getUserLK()
    {
        global $db;
        $default_user = 48;

        $user = $db->GetValue('SELECT id FROM user_users WHERE user="LK" LIMIT 1');
        if ($user > 0) return $user;
        else return $default_user;
    }


}
