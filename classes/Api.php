<?php 

class Api
{
	public function getBalance($clientIds, $simple = true)
	{
		if(!is_array($clientIds))
			$clientIds = array($clientIds);

		foreach ($clientIds as $clientId)
		{
			if(!$clientId || !preg_match("/^\d{1,6}$/", $clientId))
				throw new Exception("Неверный номер лицевого счета!");
		}

		$result = array();	
		foreach ($clientIds as $clientId)
		{

			$c = ClientCard::find_by_id($clientId);

			if(!$c)
			{
				throw new Exception("Лицевой счет не найден!");
			}

            $balance = $c->balance;

            if ($c->credit >= 0)
            {
                $billingCounter = ClientCS::getBillingCounters($clientId);
                $balance -=$billingCounter["amount_sum"];
            }

			$result[$c->id] = array("id" => $c->id, "balance" => $balance);
		}

        if ($simple)
        {
            $clientId = $clientIds[0];
            return $result[$clientId]["balance"];
        }

		return $result;
	}

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
                        "type" => self::_getPaymentTypeName($p["type"]),
                        "sum"  => $p["sum_rub"]
                        );
            }
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

    private function _getPaymentTypeName($type)
    {
        switch ($type)
        {
            case 'bank': $v = "Банк"; break;
            case 'prov': $v = "Наличные"; break;
            case 'neprov': $v = "Эл.денги"; break;
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

		$b = NewBill::first(array("conditions" => array("client_id" => $clientId, "bill_no" => $billNo)));
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
		

		return array(
				"bill" => array(
					"bill_no" => $b->bill_no,
					"is_rollback" => $b->is_rollback,
					"is_1c" => $b->is1C(),
					"lines" => $lines,
					"sum_total" => number_format($b->sum, 2, '.','')
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

			$line = self::_exportModelRow(array("id", "actual_from", "actual_to", "local_part", "domain", "box_size", "box_quota", "status", "actual"), $e);
			$line['email'] = $line['local_part'].'@'.$line['domain'];
			$ret[] = $line;

		}

		return $ret;
	}

	public function getVoipList($clientId, $isSimple = false)
	{
		$ret = array();

        $card = ClientCard::first(array("id" => $clientId));

        if (!$card)
            return $ret;

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
                                    MAX(id_tarif)
                                FROM
                                    log_tarif
                                WHERE
                                    service = "usage_voip"
                                    AND date_activation<NOW()
                                    AND id_service= u.id
                                ORDER BY
                                    date_activation DESC,
                                    id DESC
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
			$ret[] = $isSimple ? $line["number"] : $line;
		}

		return $ret;
	}

	public function getVpbxList($clientId)
	{
        $ret = array();

        foreach(NewBill::find_by_sql('
            SELECT
                `u`.`id`,
                `u`.`amount`,
                `u`.`actual_from`,
                `u`.`actual_to`,
                IF ((`u`.`actual_from` <= NOW()) AND (`u`.`actual_to` > NOW()), 1, 0) AS `actual`,
                `u`.`status`,
                `u`.`tarif_id`,
                `t`.description as tarif_name,
	            `t`.`price`,
	            `t`.`space`,
                `t`.`num_ports`,
                `d`.`name` AS city
            FROM
                `usage_virtpbx` AS `u`
            INNER JOIN `clients` ON (
                `u`.`client` = `clients`.`client`
            )
            LEFT JOIN `tarifs_virtpbx` AS `t` ON (
                `t`.`id` = `u`.`tarif_id`
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
            LIMIT 1
            ', array($clientId)) as $v)
        {
            $line =  self::_exportModelRow(array("id", "amount", "status", "actual_from", "actual_to", "actual", "tarif_name", "price", "space", "num_ports","city"), $v);
            $line['price'] = (double)round($line['price']*1.18);
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


	private static function _exportModelRow($fields, &$row)
	{
		$line = array();
		foreach ($fields as $field)
		{
			$line[$field] = Encoding::toUtf8($row->{$field});
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
	
    public static function getCollocationTarifs()
    {
        return self::_getInternetTarifs("C");
    }

    public static function getInternetTarifs()
    {
        return self::_getInternetTarifs("I");
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
                $line['price'] = (double)round($line['price']*1.18);
                $ret[] = $line;
            }
        return $ret;
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

    public static function getFreeNumbers($isSimple = false)
    {
        $ret = array();

        foreach(NewBill::find_by_sql("
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
          ") as $service)
        {
            $line = self::_exportModelRow(array("number", "beauty_level", "price", "region"), $service);
            $line['full_number'] = $line['number'];
            $line['area_code'] = substr($line['number'],1,3);
            $l = strlen($line['number']);
            $number = $line["number"];
            $line['number'] = substr($line['number'],4,($l-8)).'-'.substr($line['number'],($l-4),2).'-'.substr($line['number'],($l-2),2);
            if ($line['price'] == '') $line['price_add'] = 'Договорная';
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

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $waiting_cnt = $db->GetValue("SELECT COUNT(*) FROM usage_voip WHERE client='".$client["client"]."' AND actual_from='2029-01-01' AND actual_to='2029-01-01'");
        if ($waiting_cnt >= 5) return array('status'=>'error','message'=>'Допускается резервировать не более 5 номеров!');

        $region = $db->GetRow("select name from regions where id='".$region_id."'");
        $tarif = $db->GetRow("select id, name from tarifs_voip where id='".$tarif_id."'");
        $tarifs = $db->AllRecords($q = "select 
                                    id, dest 
                                from 
                                    tarifs_voip 
                                where 
                                    status='public' 
                                and 
                                    region='".$region_id."' 
                                and 
                                    currency='RUR' 
                                " . (($region_id == '99') ? "AND name LIKE('%".Encoding::toKOI8R('Базовый')."%')" : '') 
                                );

        $default_tarifs = array(
                        'id_tarif_local_mob'=>0,
                        'id_tarif_russia'=>0,
                        'id_tarif_intern'=>0,
                        'id_tarif_sng'=>0
                        );
        foreach ($tarifs as $r) {
            switch ($r['dest']) {
                case '1':
                    $default_tarifs['id_tarif_russia'] = $r['id'];break;
                case '2':
                    $default_tarifs['id_tarif_intern'] = $r['id'];break;
                case '3':
                    $default_tarifs['id_tarif_sng'] = $r['id'];break;
                case '5':
                    $default_tarifs['id_tarif_local_mob'] = $r['id'];break;
            }
        }

        $lines_cnt = (int)$lines_cnt;
        if ($lines_cnt > 10) $lines_cnt = 10;
        if ($lines_cnt < 1) $lines_cnt = 1;

        if (!$client || !$region || !$tarif)
            throw new Exception("Ошибка в данных!");

        $freeNumbers = self::getFreeNumbers(true);
        if (array_search($number, $freeNumbers) === false)
            //throw new Exception("Номер не свободен!");
            return array('status'=>'error','message'=>'Номер не свободен!');

        $clientNumbers = self::getVoipList($client_id, true);
        if (array_search($number, $clientNumbers) !== false)
            //throw new Exception("Номер уже используется");
            return array('status'=>'error','message'=>'Номер уже используется!');

        $message = Encoding::toKOI8R("Заказ услуги IP Телефония из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Регион: ') . $region . " (Id: $region_id)\n";
        $message .= Encoding::toKOI8R('Номер: ') . $number . "\n";
        $message .= Encoding::toKOI8R('Кол-во линий: ') . $lines_cnt . "\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif["description"] . " (Id: ".$tarif["id"].")";

        $usageVoipId = $db->QueryInsert("usage_voip", array(
                    "client"        => $client["client"],
                    "region"        => $region_id,
                    "E164"          => $number,
                    "no_of_lines"   => $lines_cnt,
                    "actual_from"   => "2029-01-01",
                    "actual_to"     => "2029-01-01",
                    "status"        => "connecting"
                    )
                );

        $db->QueryInsert("log_tarif", array(
                    "service"             => "usage_voip",
                    "id_service"          => $usageVoipId,
                    "id_tarif"            =>$tarif["id"],
                    "id_tarif_local_mob"  => $default_tarifs['id_tarif_local_mob'],
                    "id_tarif_russia"     => $default_tarifs['id_tarif_russia'],
                    "id_tarif_intern"     => $default_tarifs['id_tarif_intern'],
                    "id_tarif_sng"        => $default_tarifs['id_tarif_sng'],
                    "ts"                  => array("NOW()"),
                    "date_activation"     => array("NOW()"),
                    "dest_group"          => '0',
                    "minpayment_group"    => '0',
                    "minpayment_local_mob"=> '0',
                    "minpayment_russia"   => '0',
                    "minpayment_intern"   => '0',
                    "minpayment_sng"      => '0'
                    )
                );

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
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
        $region = $db->GetValue("select name from regions where id='".$region_id."'");
        $tarif = $db->GetRow("select id, description as name from tarifs_virtpbx where id='".$tarif_id."'");

        if (!$client || !$region || !$tarif) 
            throw new Exception("Ошибка в данных!");

        $message = Encoding::toKOI8R("Заказ услуги Виртуальная АТС из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Регион: ') . $region . " (Id: $region_id)\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif["name"] . " (Id: $tarif_id)";

        $vpbx = $db->GetRow("
                select 
                    id, 
                    IF ((`actual_from` <= NOW()) AND (`actual_to` > NOW()), 1, 0) AS `actual` 
                from 
                    usage_virtpbx 
                where 
                    client = '".$client["client"]."'
                ");

        if (!$vpbx) // добавляем VPBX, если его нет
        {
            $vpbxId = $db->QueryInsert("usage_virtpbx", array(
                        "client"        => $client["client"],
                        "actual_from"   => "2029-01-01",
                        "actual_to"     => "2029-01-01",
                        "amount"        => 1,
                        "status"        => "connecting",
                        "tarif_id"      => $tarif["id"],
                        "server_pbx_id" => 1
                        )
                    );

            if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
                return array('status'=>'ok','message'=>'Заявка принята'); 
            else
                return array('status'=>'error','message'=>'Ошибка добавления заявки');
        } elseif ($vpbx['actual'] == 0) {
            // Тариф есть, но не актуальный (заявка)
            $db->QueryUpdate("usage_virtpbx", "id", array(
                            "id"            => $vpbx['id'],
                            "tarif_id"      => $tarif["id"],
                            "server_pbx_id" => 1
                            )
                        );

            if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
                return array('status'=>'ok','message'=>'Заявка принята'); 
            else
                return array('status'=>'error','message'=>'Ошибка добавления заявки');
        } else {
            // Уже есть актуальный
            return array('status'=>'error','message'=>'Вы уже подключены.');
        }
    }

    public static function orderDomainTarif($client_id, $region_id, $tarif_id)
    {
        global $db;

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $region = $db->GetValue("select name from regions where id='".$region_id."'");
        $tarif = $db->GetValue("select description from tarifs_extra where id='".$tarif_id."'");

        $message = Encoding::toKOI8R("Заказ услуги Домен из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Регион: ') . $region . " (Id: $region_id)\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif . " (Id: $tarif_id)";

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
            return array('status'=>'ok','message'=>'Заявка принята'); 
        else 
            return array('status'=>'error','message'=>'Ошибка добавления заявки');

    }

    public static function orderEmailTarif($client_id, $domain_id, $local_part, $password)
    {
        global $db;

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $region = $db->GetValue("select name from regions where id='".$region_id."'");
        $tarif = $db->GetValue("select description from tarifs_extra where id='".$tarif_id."'");
        $domain = $db->GetValue("select domain from domains where id='".$domain_id."'");

        $message = Encoding::toKOI8R("Заказ услуги Почта из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Домен: ') . $domain . " (Id: $domain_id)\n";
        $message .= Encoding::toKOI8R('Email: ') . $local_part . "\n";
        $message .= Encoding::toKOI8R('Пароль: ') . $password;

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
            return array('status'=>'ok','message'=>'Заявка принята'); 
        else 
            return array('status'=>'error','message'=>'Ошибка добавления заявки');

    }

    public static function changeInternetTarif($client_id, $service_id, $tarif_id)
    {
        global $db;

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $address = $db->GetValue("select address from usage_ip_ports where id='".$service_id."'");
        $tarif = $db->GetValue("select name from tarifs_internet where id='".$tarif_id."'");

        $message = Encoding::toKOI8R("Заказ изменения тарифного плана услуги Интернет из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Адрес: ') . $address . " (Id: $service_id)\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif . " (Id: $tarif_id)";

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
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
        $message .= Encoding::toKOI8R('Адрес: ') . $address . " (Id: $service_id)\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif . " (Id: $tarif_id)";

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
            return array('status'=>'ok','message'=>'Заявка принята'); 
        else 
            return array('status'=>'error','message'=>'Ошибка добавления заявки');

    }

    public static function changeVoipTarif($client_id, $service_id, $tarif_id)
    {
        global $db;

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $voip = $db->GetRow("select E164, status from usage_voip where id='".$service_id."' AND client='".$client["client"]."'");
        $tarif = $db->GetValue("select name from tarifs_voip where id='".$tarif_id."'");

        $message = Encoding::toKOI8R("Заказ изменения тарифного плана услуги IP Телефония из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Номер: ') . $voip['E164'] . " (Id: $service_id)\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif . " (Id: $tarif_id)";

        if ($voip['status'] == 'connecting') {
            $db->QueryUpdate("log_tarif", "id_service", array("id_service"=>$service_id, "id_tarif" => $tarif_id));
            $message .= Encoding::toKOI8R("\n\nтариф сменен, т.к. подключения не было");
        }
        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
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
        $message .= Encoding::toKOI8R('Услуга: ') . $service_id . " (Id: $service_id)\n";
        $message .= Encoding::toKOI8R('Новый тарифный план: ') . $tarif . " (Id: $tarif_id)";

        $vpbx = $db->GetRow($q = "select id, actual_from from usage_virtpbx where client = '".$client["client"]."'");

        if ($vpbx)
        {
            if ($vpbx["actual_from"] == "2029-01-01")
            {
                $db->QueryUpdate("usage_virtpbx", "id", array("id"=>$vpbx["id"], "tarif_id" => $tarif_id));
                $message .= Encoding::toKOI8R("\n\nтариф сменен, т.к. подключения не было");
            }
            if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
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
        $message .= Encoding::toKOI8R('Домен: ') . $domain . " (Id: $service_id)\n";
        $message .= Encoding::toKOI8R('Тарифный план: ') . $tarif . " (Id: $tarif_id)";

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
            return array('status'=>'ok','message'=>'Заявка принята'); 
        else 
            return array('status'=>'error','message'=>'Ошибка добавления заявки');

    }

    public static function changeEmailTarif($client_id, $email, $password)
    {
        global $db;

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");

        $message = Encoding::toKOI8R("Заказ на изменения пароля к почтовому ящику из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Email: ') . $email . "\n";
        $message .= Encoding::toKOI8R('Новый пароль: ') . $password;

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
            return array('status'=>'ok','message'=>'Заявка принята'); 
        else 
            return array('status'=>'error','message'=>'Ошибка добавления заявки');

    }

    public static function disconnectInternet($client_id, $service_id)
    {
        global $db;

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $address = $db->GetValue("select address from usage_ip_ports where id='".$service_id."'");

        $message = Encoding::toKOI8R("Заказ на отключение услуги Интернет из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Адрес: ') . $address . " (Id: $service_id)";

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
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
        $message .= Encoding::toKOI8R('Адрес: ') . $address . " (Id: $service_id)";

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
            return array('status'=>'ok','message'=>'Заявка принята'); 
        else 
            return array('status'=>'error','message'=>'Ошибка добавления заявки');

    }
    
    public static function disconnectVoip($client_id, $service_id)
    {
        global $db;

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");
        $voip = $db->GetRow("select E164, status from usage_voip where id='".$service_id."' AND client='".$client["client"]."'");

        $message = Encoding::toKOI8R("Заказ на отключение услуги IP Телефония из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Номер: ') . $voip['E164'] . " (Id: $service_id)";

        if ($voip['status'] == 'connecting') {
            $db->QueryDelete('log_tarif', array('id_service'=>$service_id));
            $db->QueryDelete('usage_voip', array('id'=>$service_id));
            $message .= Encoding::toKOI8R("\n\номер удален, т.к. подключения не было");
        }

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
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

        $vpbx = $db->GetRow($q = "select id, actual_from from usage_virtpbx where client = '".$client["client"]."'");

        if ($vpbx)
        {
            if ($vpbx["actual_from"] == "2029-01-01")
            {
                $db->QueryDelete("usage_virtpbx", array("id" => $vpbx["id"]));

                $message .= Encoding::toKOI8R("\n\nВиртуальная АТС отключена автоматически, т.к. подключения не было");
            }

            if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
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
        $message .= Encoding::toKOI8R('Домен: ') . $domain . " (Id: $service_id)\n";

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
            return array('status'=>'ok','message'=>'Заявка принята'); 
        else 
            return array('status'=>'error','message'=>'Ошибка добавления заявки');

    }
    
    public static function disconnectEmail($client_id, $email)
    {
        global $db;

        $client = $db->GetRow("select client, company, manager from clients where id='".$client_id."'");

        $message = Encoding::toKOI8R("Заказ на отключение Почтового ящика из Личного Кабинета. \n");
        $message .= Encoding::toKOI8R('Клиент: ') . $client['company'] . " (Id: $client_id)\n";
        $message .= Encoding::toKOI8R('Почтовый ящик: ') . $email;

        if (Api::createTT($message, $client['client'], self::_getUserForTrounble($client['manager'])) > 0) 
            return array('status'=>'ok','message'=>'Заявка принята'); 
        else 
            return array('status'=>'error','message'=>'Ошибка добавления заявки');

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
                'user_author' => $user
        );

        return $tt->createTrouble($R, $user);
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
        return Api::getStatisticsInternetData($client_id, $from, $to, $detality, $route, 1);
    }


    /**
    * Возвращает все активные номера лицевого счета
    *
    * @param int $clientId id лицевого счета
    * @param bool выдать простой массив с номерами, или полный, с детальной информацией
    * @return array
    */
    public static function getClientPhoneNumbers($clientId, $isSimple = false)
    {
        global $db;

        $clientId = (int)$clientId;

        if (!$clientId)
            throw new Exception("Лицевой счет не найден!");

        $data = array();
        foreach($db->AllRecords("
                    SELECT E164, 
                    no_of_lines,
                    (select count(*) from vpbx_numbers v where (v.client_id = c.id and v.number = E164)) as is_vpbx
                    FROM 
                        `usage_voip` u, clients c 
                    where 
                            c.id = '".$clientId."' 
                        and c.client = u.client 
                        and actual_from < cast(now() as date) 
                        and actual_to >= cast(now() as date)") as $l)
        {
            if ($isSimple)
            {
                $data[$l["E164"]] = 1;
            } else {
                $data[] = array("number" => $l["E164"], "lines" => $l["no_of_lines"], "on_the_vpbx" => $l["is_vpbx"] ? 1 : 0);
            }
        }
        return  $data;
    }

    /**
    * Устанавливает, какие номера используются в vpbx'е
    *
    * @param int id лицевого счета
    * @param array массив номеров
    * @return bool
    */
    public static function setClientVatsPhoneNumbers($clientId, $numbers)
    {
        global $db;

        $clientId = (int)$clientId;

        if (!$clientId)
            throw new Exception("Лицевой счет не найден!");

        $clientNumbers = self::getClientPhoneNumbers($clientId, true);

        $db->Query("start transaction");
        $db->Query("delete from vpbx_numbers where client_id = '".$clientId."'");

        foreach($numbers as $number)
        {
            $number = preg_replace("/[^\d]/", "", $number);

            if (!$number || !isset($clientNumbers[$number]))
            {
                $db->Query("rollback");
                throw new Exception("Номер \"".$number."\" не найден в номерах клиента!");
            }
            $db->QueryInsert("vpbx_numbers", array("client_id" => $clientId, "number" => $number));
        }
        $db->Query("commit");
        return true;
    }

    public static function getServiceOptions($service, $clientId)
    {
        $o = new LkServiceOptions($service, $clientId);
        return $o->getOptions();
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

    private function _getUserForTrounble($manager)
    {
        $default_manager = "adima";

        if (defined("API__USER_FOR_TROUBLE")) return API__USER_FOR_TROUBLE;
        else if (strlen($manager)) return $manager;
        else return $default_manager;
    }
    
}
