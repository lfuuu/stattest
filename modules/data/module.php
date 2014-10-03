<?php
class m_data extends IModule {

	public function data_get_gtd()
	{
		$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';

		$gtd = Gtd::find('first', array('conditions' => array('code = ?', $code)));

		$result = array();

		if ($gtd) {
			$result = array(
				'id' => $gtd->id,
				'code' => $gtd->code,
				'country' => $gtd->country->name,
			);
		}

		header('Content-Type: application/json');
		echo json_encode($result); exit;
	}

	public function data_search_goods()
	{
		header('Content-Type: application/json');

		$query = isset($_REQUEST['query']) ? $_REQUEST['query'] : '';

		$items = array();
		foreach(Good::search($query, 25) as $good) {
			$items[] = array(
				'id' => $good->id,
				'num_id' => $good->num_id,
				'art' => $good->art,
				'name' => $good->name,
				'okei' => $good->unit->okei,
				'unit_name' => $good->unit->name,
			);
		}

		die(json_encode(array(
			'query' => $query,
			'result' => $items,
		)));
	}
}
