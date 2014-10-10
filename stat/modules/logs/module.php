<?php

class m_logs extends IModule{

	private $_inheritances = array();

	public function __construct()
	{
	//        $this->_addInheritance(new m_stats_);
	}

	public function __call($method, array $arguments = array())
	{
		foreach ($this->_inheritances as $inheritance) {
		$inheritance->invoke($method, $arguments);
		}
	}

	protected function _addInheritance(Inheritance $inheritance)
	{
		$this->_inheritances[get_class($inheritance)] = $inheritance;
		$inheritance->module = $this;
	}
	/**
	 * Action по умолчанию
	 * @param string $fixclient - имя клиента
	 */
	function logs_default($fixclient){
		$this->logs_alerts($fixclient);
	}
	/**
	 * Отображение логов оповещений
	 * @param string $fixclient - имя клиента
	 */
	function logs_alerts($fixclient){
		global $db,$design;
		
		$dateFrom = new DatePickerValues('date_from', 'first');
		$dateTo = new DatePickerValues('date_to', 'last');

		$from = $dateFrom->getTimestamp();
		$to = $dateTo->getTimestamp();
		
		DatePickerPeriods::assignStartEndMonth($dateFrom->day, 'prev_', '-1 month');
		DatePickerPeriods::assignPeriods(new DateTime());

		if ($fixclient) 
		{
			$client = $db->GetRow("select * from clients where '".addslashes($fixclient)."' in (id, client)");
			$client_id = $client['id'];
		} else {
			$client_id = 0;
		}
		
		$events = get_param_raw('events', array());
		$design->assign('events', $events);
		
		$page = get_param_raw('page', 1);
		$design->assign('page', $page);
		
		$manager = get_param_raw('manager', '');
		$design->assign('manager', $manager);
		$m=array();
		$GLOBALS['module_users']->d_users_get($m,'manager');
		$design->assign('f_manager', $m);
		
		list($logs, $pages) = $this->getLogs($client_id, $from, $to, $events, $manager, $page);
		$design->assign('pages', $pages);
		$design->assign('logs', $logs);
		
		$params = array(
			'module' => 'logs',
			'action' => 'alerts',
			'date_from' => $dateFrom->getDay(),
			'date_to' => $dateTo->getDay(),
			'events' => $events,
			'manager' => $manager
		);
		$url = http_build_query($params);
		$design->assign('url', '?' . $url . '&');

		$events_description = array(
				'min_balance' => 'Критический остаток',
				'zero_balance' => 'Финансовая блокировка',
				'day_limit' => 'Суточный лимит',
				'add_pay_notif' => 'Зачисление средств',
				'prebil_prepayers_notif' => 'Списание абонентской платы авансовым клиентам'
				
			);
		$design->assign('events_description', $events_description);
		
		$design->AddMain('logs/alerts_form.tpl');
		$design->AddMain('logs/alerts.tpl');
		
		
	}
	/**
	 * Возвращает логи оповещений
	 * @param int $client_id - id клиента
	 * @param int $from - timestamp начала выборки
	 * @param int $to - timestamp конца выборки
	 * @param array $events - список событий, которые будут отображены в отчете
	 * @param int $page - номер страницы отчета
	 */
	function getLogs($client_id, $from, $to, $events, $manager, $page)
	{
		global $db;
		$join = $condition = '';
		$fields = 'log.*, UNIX_TIMESTAMP(log.date) as timestamp, cc.data as contact_data, cc.type as contact_type';
		if ($client_id > 0) 
		{
			$condition = ' AND log.client_id = ' . $client_id;
		} else {
			$fields .= ', c.client';
			$join = ' LEFT JOIN clients as c ON log.client_id = c.id';
			if (!empty($manager))
			{
				$condition = " AND c.manager = '" . $manager . "' ";
			}
		}
		if (!empty($events))
		{
			$c = '';
			foreach ($events as &$v) $v = "'$v'";
			$condition .= " AND log.event IN (" . implode(', ', $events). ")";
		}
		$limit = 'LIMIT ' . ($page-1)*50 . ', 50';
		$logs = $db->AllRecords($q = "
			SELECT " . 
				$fields . "
			FROM 
				lk_notice_log as log
			LEFT JOIN
				client_contacts as cc ON cc.id = log.contact_id " . 
				$join . " 
			WHERE 
				log.date >= FROM_UNIXTIME(" . $from . ") AND 
				log.date <= FROM_UNIXTIME(" . $to . "+86399) " . 
				$condition . " 
			ORDER BY
				log.date DESC " .
			$limit
		);
		$total = $db->getValue("
			SELECT 
				COUNT(*)
			FROM 
				lk_notice_log as log " . 
			$join . " 
			WHERE 
				log.date >= FROM_UNIXTIME(" . $from . ") AND 
				log.date <= FROM_UNIXTIME(" . $to . "+86399) " . 
				$condition . " 
			ORDER BY
				log.date DESC
		");
		$pages = ceil($total/50);
		return array($logs, $pages);
	}
}
