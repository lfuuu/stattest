<?php

class VirtpbxStatSpace extends ActiveRecord\Model
{
	static $table_name = "virtpbx_stat_space";
	/** 
	 *	Получение данных о днях, когда не была получена информация по ВАТС
	 *	@param int $from timestamp начала периода
	 *	@param int $to timestamp конца периода
	 */
	public static function getBadStat($from, $to) 
	{
		$_dates = array();
		$params = array(
			'date_from' => date("Y-m-d", $from),
			'date_to' => date("Y-m-d", $to),
			'day' => date("d", $to),
		);
		
		$tm = new DateTime();
		$tm->setTimestamp($from);
		$one_day = new DateInterval('P1D');
		
		for ($i=1;$i<=$params['day'];$i++) 
		{
			$_dates[] = $tm->getTimestamp();
			$tm->add($one_day);
		}
		
		$options = array();
		$options['select'] = 'id, client, UNIX_TIMESTAMP(actual_from) as date_from, UNIX_TIMESTAMP(actual_to) as date_to';
		$options['conditions'] = array('actual_from <= ? AND actual_to >= ?', $params['date_to'], $params['date_from']);
		$options['group'] = 'id';
		$vpbxs = UsageVirtpbx::find('all', $options);

		foreach ($vpbxs as $v) 
		{
			$dates = $_dates;
			if ($dates[0] < $v->date_from || $dates[count($dates)-1] > $v->date_to)
			{
				foreach ($dates as $k => $date)
				{
					if ($date < $v->date_from || $date > $v->date_to)
					{
						unset($dates[$k]);
					}
				}
			}
			$pbx_dates = array();
			$options = array();
			$options['select'] = 'UNIX_TIMESTAMP(date) as ts';
			$options['conditions'] = array(
				'numbers IS NOT NULL AND use_space IS NOT NULL AND usage_id = ? AND date >= ? AND date <= ?',
				$v->id, 
				$params['date_from'], 
				$params['date_to']
			);
			$temp = self::find('all', $options);
			if (!empty($temp)) 
			{
				foreach ($temp as $ts)
				{
					$pbx_dates[] = $ts->ts;
				}
				$timestamps = array_diff($dates, $pbx_dates);
			} else {
				$timestamps = $dates;
			}
			if (!empty($timestamps))
			{
				$data[$v->id]['client'] = $v->client;
				$data[$v->id]['ts'] = $timestamps;
			}
		}
		return $data;
	}
}
