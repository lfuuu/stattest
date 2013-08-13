<?php

class m_incomegoods extends IModule{
	public function incomegoods_order_list()
	{
		global $design, $user;

		$filter = array(
			'status' => 'all',
			'manager' => $user->Get('id'),
		);

		$filter = array_merge($filter, isset($_GET['filter']) ? $_GET['filter'] : array());

		$where = 'true';
		$whereData = array();

		if ($filter['manager'] != 'all') {
			$where .= ' and manager_id=? ';
			$whereData[] = $filter['manager'];
		}

		$statusCounter = array('all' => array('name' => 'Все', 'count' => 0));
		foreach(GoodsIncomeOrder::$statuses as $key => $name)
			$statusCounter[$key] = array('name' => $name, 'count' => 0);

		$statuses =
			GoodsIncomeOrder::find_by_sql("
				select `status`, count(*) as count from g_income_order
				where {$where}
				group by `status`
			", $whereData);

		foreach($statuses as $status) {
			$statusCounter['all']['count'] += $status->count;
			if (!isset($statusCounter[$status->status])) continue;
			$statusCounter[$status->status]['count'] += $status->count;
		}

		if ($filter['status'] != 'all') {
			$where .= ' and status=? ';
			$whereData[] = $filter['status'];
		}

		array_unshift($whereData, $where);

		$list = GoodsIncomeOrder::all(
			array(
				'order' => 'date desc',
				'limit' => 100,
				'conditions' => $whereData,
			)
		);

		$design->assign('list', $list);
		$design->assign('statusCounter', $statusCounter);
		$design->assign('qfilter', $filter);
		$design->assign('users', User::find('all', array('order' => 'name')));
		$design->AddMain('incomegoods/order_list.tpl');
	}

	public function incomegoods_order_view()
	{
		global $design;

		if (!isset($_GET['id'])) throw new IncorrectRequestParametersException();


		$order = GoodsIncomeOrder::find($_GET['id']);

		$design->assign('order', $order);
		$design->AddMain('incomegoods/order_view.tpl');
	}

	public function incomegoods_order_edit()
	{
		global $design, $fixclient_data, $user;

		if (!isset($_GET['id'])) throw new IncorrectRequestParametersException();

		if ($_GET['id'] == '') {
			if (!isset($fixclient_data['id'])) {
				trigger_error('Выберите клиента...'); return;
			}
			$order = new GoodsIncomeOrder();
			$order->active = true;
			$order->currency = Currency::RUB;
			$order->client_card_id = $fixclient_data['id'];
			$order->organization_id = Organization::DEFAULT_FOR_INCOMES;
			$order->store_id = Store::MAIN_STORE;
			$order->status = GoodsIncomeOrder::STATUS_AGREED;
			$order->price_includes_nds = true;
			$order->manager_id = $user->Get('id');
		} else {
			$order = GoodsIncomeOrder::find($_GET['id']);
		}


		$design->assign('order', $order);
		$design->assign('statuses', GoodsIncomeOrder::$statuses);
		$design->assign('organizations', Organization::find('all', array('order' => 'name')));
		$design->assign('users', User::find('all', array('order' => 'name')));
		$design->assign('stores', Store::find('all', array('order' => 'name')));
		$design->assign('currencies', Currency::find('all'));
		$design->AddMain('incomegoods/order_edit.tpl');
	}

	public function incomegoods_order_save()
	{
		if (!isset($_POST) || !isset($_POST['id']) || !isset($_POST['item']))
			throw new IncorrectRequestParametersException();

		$_POST = Encoding::toKoi8r($_POST);

		$list = array();
		foreach($_POST['item'] as $line) {
			if ($incoming_date = DateTime::createFromFormat('d.m.Y', $line['incoming_date']))
				$incoming_date = $incoming_date->format(DateTime::ATOM);
			else
				$incoming_date = '0001-01-01T00:00:00';

			$list[] = array(
				'Номенклатура' => $line['good_id'],
				'Количество' => $line['amount'],
				'Цена' => $line['price'],
				'КодСтроки' => $line['line_code'],
				'ДатаПоступления' => $incoming_date,
			);
		}

		if ($external_date = DateTime::createFromFormat('d.m.Y', $_POST['external_date']))
			$external_date = $external_date->format(DateTime::ATOM);
		else
			$external_date = '0001-01-01T00:00:00';

		$data = array(
			'Код1С' => $_POST['id'],
			'Проведен' => (bool)$_POST['active'],
			'КодКонтрагента' => $_POST['client_card_id'],
			'НомерПоДаннымПоставщика' => $_POST['external_number'],
			'ДатаПоДаннымПоставщика' => $external_date,
			'Статус' => $_POST['status'],
			'Организация' => $_POST['organization_id'],
			'Склад' => $_POST['store_id'],
			'Валюта' => $_POST['currency'],
			'ЦенаВключаетНДС' => (bool)$_POST['price_includes_nds'],
			'Менеджер' => $_POST['manager_id'],
			'СписокПозиций' => $list,
		);

		$order = Sync1C::getClient()->saveGoodsIncomeOrder($data);

		header('Content-Type: application/json');
		die(json_encode(array(
			'url' => '?module=incomegoods&action=order_view&id=' . $order->id
		)));
	}

	public function incomegoods_document_view()
	{
		global $design;

		if (!isset($_GET['id'])) throw new IncorrectRequestParametersException();

		$document = GoodsIncomeDocument::find($_GET['id']);

		$design->assign('document', $document);
		$design->assign('order', $document->order);
		$design->assign('lines', $this->getDocumentLines($document));
		$design->AddMain('incomegoods/document_view.tpl');
	}


	public function incomegoods_document_edit()
	{
		global $design;

		if (!isset($_GET['id'])) throw new IncorrectRequestParametersException();

		if ($_GET['id'] == '') {
			$document = new GoodsIncomeDocument();
			$order = GoodsIncomeOrder::find($_GET['order_id']);
			$document->active = true;
			$document->order_id = $order->id;
			$document->currency = $order->currency;
			$document->client_card_id = $order->client_card_id;
			$document->organization_id = $order->organization_id;
			$document->store_id = $order->store_id;
			$document->price_includes_nds = $order->price_includes_nds;
		} else {
			$document = GoodsIncomeDocument::find($_GET['id']);
		}

		$design->assign('document', $document);
		$design->assign('order', $document->order);
		$design->assign('lines', $this->getDocumentLines($document));
		$design->assign('countries', Country::find('all', array('order' => 'name asc')));
		$design->AddMain('incomegoods/document_edit.tpl');
	}

	public function incomegoods_document_save()
	{
		$_POST = Encoding::toKoi8r($_POST);

		$list = array();
		foreach($_POST['item'] as $line) {
			$list[] = array(
				'Номенклатура' => $line['good_id'],
				'Количество' => $line['amount'],
				'Цена' => $line['price'],
				'КодСтроки' => $line['line_code'],
				'КодНомерГТД' => $line['gtd_id'],
			);
		}

		$data = array(
			'Код1С' => $_POST['id'],
			'ЗаказКод1С' => $_POST['order_id'],
			'Проведен' => (bool)$_POST['active'],
			'СписокПозиций' => $list,
		);

		$document = Sync1C::getClient()->saveGoodsIncomeDocument($data);

		header('Content-Type: application/json');
		die(json_encode(array(
			'url' => '?module=incomegoods&action=document_view&id=' . $document->id
		)));
	}

	public function incomegoods_store_view()
	{
		global $design;

		$document = GoodsIncomeStore::find($_GET['id']);

		$design->assign('document', $document);
		$design->AddMain('incomegoods/store_view.tpl');
	}

	public function incomegoods_add_gtd()
	{
		$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
		$country = isset($_REQUEST['country']) ? $_REQUEST['country'] : '';
		if (!$code && !$country) {
			throw new IncorrectRequestParametersException();
		}

		$data = array(
			'Код1С' => '',
			'Код' => $code,
			'Страна' => $country,
		);
		$gtd = Sync1C::getClient()->saveGtd($data);

		header('Content-Type: application/json');
		die(json_encode(array(
			'id' => $gtd->id,
			'code' => $gtd->code,
			'country' => iconv('koi8-r', 'utf-8', $gtd->country->name),
		)));
	}

	private function getDocumentLines($document)
	{
		$pos = 0;

		$orderLines = array();
		$documentLines = array();

		foreach($document->order->lines as $line) {
			$orderLines[$line->line_code] = $line;
		}

		$items = array();
		if (!$document->is_new_record()) {
			foreach($document->lines as $line) {
				if ($line->line_code > 0) {
					$documentLines[$line->line_code] = $line;

					$items[] = array(
						'pos' => $pos++,
						'line_code' => $line->line_code,
						'good_id' => $line->good_id,
						'good_num_id' => $line->good->num_id,
						'good_name' => $line->good->name,
						'amount' => $line->amount,
						'price' => $line->price,
						'sum' => $line->sum,
						'sum_nds' => $line->sum_nds,
						'gtd' => $line->gtd,
						'order_amount' => isset($orderLines[$line->line_code]) ? $orderLines[$line->line_code]->amount : 0,
						'order_price' => isset($orderLines[$line->line_code]) ? $orderLines[$line->line_code]->price : 0,
						'order_line' => isset($orderLines[$line->line_code]) ? $orderLines[$line->line_code] : null,
					);
				}
			}
		}

		if ($document->order->ready) {
			foreach($document->order->lines as $line) {
				if (!isset($documentLines[$line->line_code])) {
					$items[] = array(
						'pos' => $pos++,
						'line_code' => $line->line_code,
						'good_id' => $line->good_id,
						'good_num_id' => $line->good->num_id,
						'good_name' => $line->good->name,
						'amount' => 0,
						'price' => $line->price,
						'sum' => 0,
						'sum_nds' => 0,
						'gtd' => null,
						'order_amount' => $line->amount,
						'order_price' => $line->price,
						'order_line' => $line,
					);
				}
			}
		}

		if (!$document->is_new_record()) {
			foreach($document->lines as $line) {
				if ($line->line_code <= 0) {
					$items[] = array(
						'pos' => $pos++,
						'line_code' => $line->line_code,
						'good_id' => $line->good_id,
						'good_num_id' => $line->good->num_id,
						'good_name' => $line->good->name,
						'price' => $line->price,
						'amount' => $line->amount,
						'sum' => $line->sum,
						'sum_nds' => $line->sum_nds,
						'gtd' => $line->gtd,
						'order_amount' => 0,
						'order_price' => 0,
						'order_line' => null,
					);
				}
			}
		}

		return $items;
	}
}
