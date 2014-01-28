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

			$billingCounter = ClientCS::getBillingCounters($clientId);

			$result[$c->id] = array("id" => $c->id, "balance" => $c->balance-$billingCounter["amount_sum"]);
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

		$bills = array();
		foreach ($R as $r)
		{
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
				$bill["pays"][] = array(
					"no"   => $p["payment_no"], 
					"date" => $p["payment_date"], 
					"type" => $p["type"], 
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

		if(!defined('API__print_bill_url') || !APP__print_bill_url)
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
					"price"     => $l->price, 
					"sum"       => $l->sum
					);
		}
		

		return array(
				"bill" => array(
					"bill_no" => $b->bill_no,
					"is_rollback" => $b->is_rollback,
					"is_1c" => $b->is1C(),
					"lines" => $lines,
					"sum_total" => $b->sum
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

	public function getVoipList($clientId)
	{
		$ret = array();

		foreach(NewBill::find_by_sql('
				SELECT
					`u`.*,
					`t`.`name` AS `tarif_name`
				FROM
				(
				 SELECT
					 `u`.`id`,
					 `u`.`E164` AS `number`,
					 `u`.`no_of_lines`,
					 `u`.`actual_from`,
					 `u`.`actual_to`,
					 IF ((`u`.`actual_from` <= NOW()) AND (`u`.`actual_to` > NOW()), 1, 0) AS `actual`,
					 IF ((`u`.`actual_from` <= (NOW()+INTERVAL 5 DAY)), 1, 0) AS `actual5d`,
					 MAX(`l`.`date_activation`) AS `tarif_date_activation`
				 FROM
					 `usage_voip` AS `u`
				 INNER JOIN `clients` ON (`u`.`client` = `clients`.`client`)
				 LEFT JOIN `log_tarif` AS `l` on (
						`l`.`service` = "usage_voip" 
					AND `l`.`id_service` = `u`.`id` 
					AND `l`.`date_activation` <= CAST(NOW() as DATE)
					)
				 WHERE
					 `clients`.`id`=?
				 GROUP BY
					 `u`.`E164`,
					 `u`.`no_of_lines`,
					 `u`.`actual_from`,
					 `u`.`actual_to`,
					 `l`.`date_activation`
				 ) as `u`
				 LEFT JOIN `log_tarif` AS `l` ON (
					`l`.`service` = "usage_voip" 
					AND `l`.`id_service` = `u`.`id` 
					AND `l`.`date_activation` = `u`.`tarif_date_activation`
					)
				 LEFT JOIN `tarifs_voip` AS `t` ON (
					`t`.`id` = `l`.`id_tarif`
					)
				 ORDER BY
					 `actual` DESC,
					 `u`.`actual_from` DESC
				', array($clientId)) as $v)
		{
			$line =  self::_exportModelRow(array("id", "number", "no_of_lines", "actual_from", "actual_to", "actual", "actual5d", "tarif_date_activation", "tarif_name"), $v);
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
}
