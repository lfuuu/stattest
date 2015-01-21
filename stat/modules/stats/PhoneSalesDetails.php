<?php
	/**
	*	Класс предназначен для получение подробной информации о значение цифр в отчете "Продажи номеров"
	*/
	class PhoneSalesDetails
	{
		/** 
		*	Обработка параметров запроса и вызов соответствующей функии
		*/
		public static function getDetails()
		{
			$type = get_param_raw('type', '');
			$subtype = get_param_raw('subtype', '');
			$disabled = get_param_raw('disabled', false);
			$month = get_param_integer('month', 0);
			$region = get_param_integer('region', '');
			$channel_id = get_param_integer('channel_id', 0);
			if (!$type || (!$region && $type != 'channels'))
			{
				return false;
			}
			switch ($type)
			{
				case 'numbers':
					return self::getNumbersDetails($region, $month, $subtype, $disabled);
				case 'vpbx':
					return self::getVpbxDetails($region, $month, $disabled);
				case 'sums':
					return self::getSumsDetails($region, $month);
				case 'channels':
					return self::getChannelsDetails($channel_id, $month);
			}
			return false;
		}
		/** 
		*	Получение детализации о подключенных номерах
		*	@param int $region ID региона по которому идет детализация
		*	@param int $month месяц по которому идет детализация
		*		если не задан, то берется информация об актуальных номерах на текущий день
		*	@param string $subtype timestamp начала периода
		*		пустая строка - берется информация о соединительных линиях
		*		"nums" - берется информация о номерах
		*		"no_nums" - берется информация о линиях без номера
		*		"8800" - берется информация о 8800 номерах
		*	@param bool $disabled (работает если только задан $month)
		*		true - берет номера которые подключили в указанном месяце
		*		false - берет номера которые отключили в указанном месяце
		*/
		private static function getNumbersDetails($region, $month, $subtype = '', $disabled = false)
		{
			global $db,$design;
			$title = 'Детальная информация о [dis] [nums] [month] по региону [region]';
			$search = array('[dis]', '[nums]', '[month]', '[region]');
			$replace = array();
			$field = ($disabled) ? 'actual_to' : 'actual_from';
			$replace[0] = (!$disabled) ? 'проданных' : 'отключенных';
			$add_fields = '';
			if (empty($month)) 
			{
				$replace[0] = 'подключенных';
				$condition = "CAST(NOW() AS DATE) BETWEEN u.actual_from AND u.actual_to";
			} else {
				$ts = mktime(0,0,0,$month,1,date('Y'));
				if ($ts > time()) {
					$ts = strtotime("-1 year", $ts);
				}
				$design->assign('ts', $ts);
				$condition = "u.".$field." BETWEEN '" . date('Y-m-d', $ts) . "' AND '" . date('Y-m-t', $ts) . "'";
			}
			$replace[1] = 'соединительных линиях';
			if ($subtype == 'nums') {
				$condition .= " AND LENGTH(u.E164) > 4 AND u.E164 NOT LIKE '7800%'";
				$replace[1] = 'номерах';
			} elseif ($subtype == 'no_nums') {
				$condition .= " AND LENGTH(u.E164) < 5";
				$replace[1] = 'линиях без номера';
			} elseif ($subtype == '8800') {
				$condition .= " AND u.E164 LIKE '7800%'";
				$replace[1] = '8800 номерах';
			}
			$phones = $db->AllRecords('
				select 
					u.E164,
					u.no_of_lines,
					u.client,
					c.id as client_id, 
					UNIX_TIMESTAMP(u.actual_from) as actual_from,
					if (u.actual_to >= "2029-01-01", "--", UNIX_TIMESTAMP(u.actual_to)) as actual_to, 
					TV.name as tarif 
				from 
					usage_voip u 
				LEFT JOIN 
					log_tarif as LT ON u.id = LT.id_service
				LEFT JOIN 
					tarifs_voip as TV ON TV.id = LT.id_tarif
				LEFT JOIN
					clients as c ON c.client = u.client 
				where ' . 
					$condition . 
					' AND u.region = ' . $region . ' AND 
					LT.service = "usage_voip" AND 
					LT.id = (
						SELECT id 
						FROM log_tarif as b
						WHERE
							date_activation = (
								SELECT MAX(date_activation)
								FROM log_tarif 
								WHERE 
									CAST(NOW() as DATE) >= date_activation AND 
									service = "usage_voip" AND 
									id_service = b.id_service
								) AND 
							id_service = LT.id_service
						ORDER BY
								ts desc
						LIMIT 0,1
					)
				order by 
					client_id, u.actual_from'
			);
			$totals = $db->GetRow('
				select 
					count(u.id) as num_count,
					sum(u.no_of_lines) as count_lines 
				from 
					usage_voip u 
				where ' . 
					$condition . 
					' AND u.region = ' . $region
			);

			$design->assign('totals', $totals);
			$replace[2] = (isset($ts)) ? 'за ' . mdate('месяц Y', $ts) . ' года ' : ''; 
			$replace[3] = $db->GetValue("SELECT name FROM regions WHERE id = " . $region);
			$title = str_replace($search, $replace, $title);
			$design->assign('title', $title);
			$design->assign('phones', $phones);
			$design->ProcessEx('errors.tpl');
			$design->ProcessEx('stats/phone_sales_details_numbers.tpl');
		}
		/** 
		*	Получение детализации о подключенных Виртуальных АТС
		*	@param int $region ID региона по которому идет детализация
		*	@param int $month месяц по которому идет детализация
		*		если не задан, то берется информация об актуальных ВАТС на текущий день
		*	@param bool $disabled (работает если только задан $month)
		*		true - берет ВАТС которые подключили в указанном месяце
		*		false - берет ВАТС которые отключили в указанном месяце
		*/
		private static function getVpbxDetails($region, $month, $disabled = false)
		{
			global $db,$design;
			
			$title = 'Детальная информация о [dis] виртуальных АТС [month] по региону [region]';
			$search = array('[dis]', '[month]', '[region]');
			$replace = array();
			$field = ($disabled) ? 'actual_to' : 'actual_from';
			$replace[0] = (!$disabled) ? 'проданных' : 'отключенных';
			
			if (empty($month)) 
			{
				$replace[0] = 'подключенных';
				$condition = "CAST(NOW() AS DATE) BETWEEN u.actual_from AND u.actual_to";
			} else {
				$ts = mktime(0,0,0,$month,1,date('Y'));
				$design->assign('ts', $ts);
				$condition = "u.".$field." BETWEEN '" . date('Y-m-d', $ts) . "' AND '" . date('Y-m-t', $ts) . "'";
			}
			$vpbxs = $db->AllRecords($q = '
				select 
					u.id,
					u.client,
					c.id as client_id,
					UNIX_TIMESTAMP(u.actual_from) as actual_from,
					if (u.actual_to >= "2029-01-01", "--", UNIX_TIMESTAMP(u.actual_to)) as actual_to,
					TV.description as tarif
				from 
					usage_virtpbx u 
				left join 
					clients as c ON u.client = c.client 
				LEFT JOIN 
					log_tarif as LT ON u.id = LT.id_service
				LEFT JOIN 
					tarifs_virtpbx as TV ON TV.id = LT.id_tarif 
				where ' . 
					$condition . 
					' AND c.region = ' . $region . '  AND 
					LT.service = "usage_virtpbx" AND 
					LT.id = (
						SELECT id 
						FROM log_tarif as b
						WHERE
							date_activation = (
								SELECT MAX(date_activation)
								FROM log_tarif 
								WHERE 
									CAST(NOW() as DATE) >= date_activation AND 
									service = "usage_virtpbx" AND 
									id_service = b.id_service
								) AND 
							id_service = LT.id_service
						ORDER BY
								ts desc
						LIMIT 0,1
					)
				order by
					client_id, u.actual_from'
			);
			$total = $db->GetValue('
				select 
					COUNT(u.id) 
				from 
					usage_virtpbx u 
				left join 
					clients as c ON u.client = c.client 
				where ' . 
					$condition . 
					' AND c.region = ' . $region
			);
			$design->assign('total', $total);
			$design->assign('region', $region);
			$replace[1] = (isset($ts)) ? 'за ' . mdate('месяц Y', $ts) . ' года ' : '';
			$replace[2] = $db->GetValue("SELECT name FROM regions WHERE id = " . $region);
			$title = str_replace($search, $replace, $title);
			$design->assign('title', $title);
			$design->assign('vpbxs', $vpbxs);
			$design->ProcessEx('errors.tpl');
			$design->ProcessEx('stats/phone_sales_details_vpbx.tpl');
		}
		/** 
		*	Получение детализации о выставленых счетах
		*	@param int $region ID региона по которому идет детализация
		*	@param int $month месяц по которому идет детализация
		*		если не задан, то берется текущий месяц
		*/
		private static function getSumsDetails($region, $month)
		{
			global $db,$design;
			if (empty($month)) 
			{
				$month = date('n');
			} 
			
			$ts = mktime(0,0,0,$month,1,date('Y'));
			$design->assign('ts', $ts);
			$condition = "b.bill_date BETWEEN '" . date('Y-m-d', $ts) . "' AND '" . date('Y-m-t', $ts) . "'";
			
			$bills = $db->AllRecords($q = '
				select 
					b.sum,
					b.client_id,
					UNIX_TIMESTAMP(b.bill_date) as bill_date,
					b.bill_no,
					b.currency,
					c.client 
				from 
					newbills b 
				left join 
					clients as c ON b.client_id = c.id 
				where ' . 
					$condition . 
					' AND c.region = ' . $region . ' AND 
					b.sum > 0 AND 
					c.status IN ("testing", "conecting", "work") AND 
					c.type IN ("org", "priv") 
				order by 
					b.client_id, b.bill_date'
			);
			$total = $db->GetValue('
				select 
					SUM(
						IF(b.currency="RUB", b.sum,
							IF (b.inv_rub > 0, b.inv_rub,
								(SELECT 
									rate 
								FROM 
									bill_currency_rate 
								WHERE 
									date = b.bill_date)
								*b.sum)
						)
					)
				from 
					newbills b 
				left join 
					clients as c ON b.client_id = c.id 
				where ' . 
					$condition . 
					' AND c.region = ' . $region . ' AND 
					b.sum > 0 AND 
					c.status IN ("testing", "conecting", "work") AND 
					c.type IN ("org", "priv") ');
			$design->assign('total', $total);
			$design->assign('region', $region);
			$region_name = $db->GetValue("SELECT name FROM regions WHERE id = " . $region);
			$design->assign('region_name', $region_name);
			$design->assign('bills', $bills);
			$design->ProcessEx('errors.tpl');
			$design->ProcessEx('stats/phone_sales_details_sums.tpl');
		}
		/** 
		*	Получение детализации о продажах менеджера
		*	@param int $channel_id ID менеджера
		*	@param int $month месяц по которому идет детализация
		*/
		private static function getChannelsDetails($channel_id, $month)
		{
			global $db,$design;
			$ts = mktime(0,0,0,$month,1,date('Y'));
			$design->assign('ts', $ts);
			$condition = "u.actual_from BETWEEN '" . date('Y-m-d', $ts) . "' AND '" . date('Y-m-t', $ts) . "'";
			
			$res = $db->AllRecords($q = "
				SELECT 
					u.E164 as phone, 
					u.no_of_lines,
					c.id as client_id, 
					c.client,
					ifnull(c.created >= date_add('".date('Y-m-d', $ts)."',interval -1 month), 0) as is_new, 
					s.name as sale_channel,
					s.courier_id,
					UNIX_TIMESTAMP(u.actual_from) as actual_from,
					if (u.actual_to >= '2029-01-01', '--', UNIX_TIMESTAMP(u.actual_to)) as actual_to, 
					TV.name as tarif 
				FROM 
					usage_voip u
				LEFT JOIN 
					clients c on c.client=u.client
				LEFT JOIN 
					sale_channels s on s.id=c.sale_channel
				LEFT JOIN 
					log_tarif as LT ON u.id = LT.id_service
				LEFT JOIN 
					tarifs_voip as TV ON TV.id = LT.id_tarif
				WHERE 
					".$condition." AND c.sale_channel = ".$channel_id." AND 
					LT.service = 'usage_voip' AND 
					LT.id = (
						SELECT id 
						FROM log_tarif as b
						WHERE
							date_activation = (
								SELECT MAX(date_activation)
								FROM log_tarif 
								WHERE 
									CAST(NOW() as DATE) >= date_activation AND 
									service = 'usage_voip' AND 
									id_service = b.id_service
								) AND 
							id_service = LT.id_service
						ORDER BY
								ts desc
						LIMIT 0,1
					)
				ORDER BY  
					is_new DESC, client_id  ");
			
			$res_vpbx = $db->AllRecords($q1="
				SELECT 
					u.id, 
					c.id as client_id, 
					c.client,
					ifnull(c.created >= date_add('".date('Y-m-d', $ts)."',interval -1 month), 0) as is_new, 
					s.name as sale_channel,
					s.courier_id,
					UNIX_TIMESTAMP(u.actual_from) as actual_from,
					if (u.actual_to >= '2029-01-01', '--', UNIX_TIMESTAMP(u.actual_to)) as actual_to,
					TV.description as tarif 
				FROM 
					usage_virtpbx u 
				LEFT JOIN  
					clients c on c.client=u.client
				LEFT JOIN 
					sale_channels s on s.id=c.sale_channel
				LEFT JOIN 
					log_tarif as LT ON u.id = LT.id_service
				LEFT JOIN 
					tarifs_virtpbx as TV ON TV.id = LT.id_tarif 
				WHERE 
					".$condition." AND c.sale_channel = ".$channel_id."  AND 
					LT.service = 'usage_virtpbx' AND 
					LT.id = (
						SELECT id 
						FROM log_tarif as b
						WHERE
							date_activation = (
								SELECT MAX(date_activation)
								FROM log_tarif 
								WHERE 
									CAST(NOW() as DATE) >= date_activation AND 
									service = 'usage_virtpbx' AND 
									id_service = b.id_service
								) AND 
							id_service = LT.id_service
						ORDER BY
								ts desc
						LIMIT 0,1
					)
				ORDER BY  
					is_new DESC, client_id ");
					
			$courier_id = (isset($res[0])) ? $res[0]['courier_id'] : $res_vpbx[0]['courier_id'];
			$visits = array();
			if ($courier_id)
			{
				$visits = $db->AllRecords($q = '
					SELECT 
						tt.client, tt.id, UNIX_TIMESTAMP(st.date_start) as date, tt.bill_no
					FROM 
						tt_stages as st
					LEFT JOIN 
						tt_troubles as tt ON tt.id = st.trouble_id 
					LEFT JOIN 
						tt_doers as td ON td.stage_id = st.stage_id 
					WHERE 
						tt.id IN (SELECT 
								DISTINCT(t.id) 
							FROM 
								tt_doers as d
							LEFT JOIN 
								tt_stages as s ON d.stage_id = s.stage_id 
							LEFT JOIN 
								tt_troubles as t ON s.trouble_id = t.id 
							WHERE 
								d.doer_id = ' . $courier_id . ' 
								AND 
								s.date_start>="' . date('Y-m-d', strtotime('-1 month',$ts)) . ' 00:00:00" 
								AND
								s.date_start<="' . date('Y-m-t', strtotime('+1 month',$ts)) . ' 23:59:59" 
							) 
						AND 
						st.state_id = 4 
						AND 
						st.date_start>="' . date('Y-m-d', $ts) . ' 00:00:00" 
						AND
						st.date_start<="' . date('Y-m-t', $ts) . ' 23:59:59" 
						AND  
						td.doer_id = ' . $courier_id . '
					ORDER BY 
						date
				');
			}
			$design->assign('visits', $visits);
			$design->assign('res', $res);
			$design->assign('res_vpbx', $res_vpbx);
			$design->ProcessEx('errors.tpl');
			$design->ProcessEx('stats/phone_sales_details_channels.tpl');
		}
	}
?>
